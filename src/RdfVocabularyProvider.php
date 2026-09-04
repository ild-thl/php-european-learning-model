<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use SimpleXMLElement;

final class RdfVocabularyProvider implements VocabularyProvider {

    public function __construct(
        private readonly VocabularyResourceFetcher $fetcher,
        private readonly int $maxResponseBytes = 5_000_000,
    ) {
        if ($maxResponseBytes < 1) {
            throw new \InvalidArgumentException('Vocabulary response limit must be positive.');
        }
    }

    public function getScheme(string $schemeId): ?VocabularyScheme {
        try {
            $document = $this->fetcher->fetch($schemeId);
            if (strlen($document) > $this->maxResponseBytes) {
                throw new InvalidCredentialException('Vocabulary response exceeds the configured size limit.');
            }
            $xml = $this->parseXml($document);
            $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            $xml->registerXPathNamespace('skos', 'http://www.w3.org/2004/02/skos/core#');
            $xml->registerXPathNamespace('xml', 'http://www.w3.org/XML/1998/namespace');

            $concepts = [];
            foreach ($xml->xpath('//skos:Concept | //rdf:Description[skos:prefLabel]') ?: [] as $node) {
                $concept = $this->parseConcept($node, $schemeId);
                if ($concept !== null) {
                    $concepts[] = $concept;
                }
            }
            $schemeNode = $this->findResource($xml, $schemeId);
            if ($schemeNode === null && $concepts === []) {
                return null;
            }

            return new VocabularyScheme(
                $schemeId,
                new ConceptScheme($schemeId),
                $concepts,
                $this->parseLabel($schemeNode),
                $schemeId,
            );
        } catch (InvalidCredentialException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new InvalidCredentialException('Unable to load RDF vocabulary resource.', 0, $exception);
        }
    }

    public function getConcept(string $conceptId, string $schemeId): ?Concept {
        return $this->getScheme($schemeId)?->find($conceptId);
    }

    private function parseXml(string $document): SimpleXMLElement {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($document, SimpleXMLElement::class, LIBXML_NONET);
        libxml_clear_errors();
        if ($xml === false) {
            throw new InvalidCredentialException('Vocabulary resource is not valid RDF/XML.');
        }

        return $xml;
    }

    private function parseConcept(SimpleXMLElement $node, string $schemeId): ?Concept {
        $id = (string) $node->attributes('rdf', true)->about;
        if ($id === '') {
            return null;
        }
        $inScheme = $node->children('skos', true)->inScheme;
        $inSchemeId = (string) $inScheme->attributes('rdf', true)->resource;
        if ($inSchemeId !== $schemeId) {
            return null;
        }
        $labels = [];
        foreach ($node->children('skos', true)->prefLabel as $label) {
            $language = (string) $label->attributes('xml', true)->lang;
            if ($language !== '') {
                $labels[$language] = (string) $label;
            }
        }
        if ($labels === []) {
            throw new InvalidCredentialException('Vocabulary concept is missing a language-tagged prefLabel.');
        }
        $notation = (string) $node->children('skos', true)->notation;

        return new Concept($id, new LocalizedString($labels), new ConceptScheme($schemeId), $notation ?: null);
    }

    private function findResource(SimpleXMLElement $xml, string $resource): ?SimpleXMLElement {
        foreach ($xml->xpath('//*[@rdf:about]') ?: [] as $node) {
            if ((string) $node->attributes('rdf', true)->about === $resource) {
                return $node;
            }
        }

        return null;
    }

    private function parseLabel(?SimpleXMLElement $node): ?LocalizedString {
        if ($node === null) {
            return null;
        }
        $labels = [];
        foreach ($node->children('skos', true)->prefLabel as $label) {
            $language = (string) $label->attributes('xml', true)->lang;
            if ($language !== '') {
                $labels[$language] = (string) $label;
            }
        }

        return $labels === [] ? null : new LocalizedString($labels);
    }
}
