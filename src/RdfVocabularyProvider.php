<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use SimpleXMLElement;

final class RdfVocabularyProvider implements VocabularyProvider {

    public function __construct(
        private readonly VocabularyResourceFetcher $fetcher,
        private readonly int $maxResponseBytes = 5_000_000,
        private readonly int $maxEnrichedConcepts = 100,
    ) {
        if ($maxResponseBytes < 1) {
            throw new \InvalidArgumentException('Vocabulary response limit must be positive.');
        }
        if ($maxEnrichedConcepts < 1) {
            throw new \InvalidArgumentException('Vocabulary enrichment limits must be positive.');
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
            $conceptIds = [];
            $conceptNodes = $xml->xpath('//skos:Concept | //rdf:Description[skos:prefLabel or skos:inScheme]') ?: [];
            foreach ($conceptNodes as $node) {
                $conceptId = (string) $node->attributes('rdf', true)->about;
                if ($conceptId === '' || $conceptId === $schemeId) {
                    continue;
                }
                $inScheme = $node->children('skos', true)->inScheme;
                $inSchemeId = (string) $inScheme->attributes('rdf', true)->resource;
                if ($inSchemeId === '' && $node->getName() !== 'Concept') {
                    continue;
                }
                if ($inSchemeId !== '' && $inSchemeId !== $schemeId) {
                    continue;
                }
                $concept = $this->parseConcept($node, $schemeId, true);
                if ($concept !== null) {
                    $concepts[] = $concept;
                } else {
                    $conceptIds[] = $conceptId;
                }
            }
            $conceptIds = array_unique($conceptIds);
            if (count($conceptIds) > $this->maxEnrichedConcepts) {
                throw new InvalidCredentialException(
                    'Vocabulary contains too many unlabeled concepts for snapshot loading; use paged search.',
                );
            }
            foreach ($conceptIds as $conceptId) {
                $concept = $this->fetchConcept($conceptId, $schemeId);
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
            throw new InvalidCredentialException(
                'Unable to load RDF vocabulary resource: ' . $exception->getMessage(),
                0,
                $exception,
            );
        }
    }

    public function getConcept(string $conceptId, string $schemeId): ?Concept {
        return $this->getScheme($schemeId)?->find($conceptId);
    }

    public function getConceptByNotation(string $notation, string $schemeId): ?Concept {
        return $this->getScheme($schemeId)?->findByNotation($notation);
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

    private function parseConcept(
        SimpleXMLElement $node,
        string $schemeId,
        bool $allowMissingLabel = false,
    ): ?Concept {
        $id = (string) $node->attributes('rdf', true)->about;
        if ($id === '') {
            return null;
        }
        $inScheme = $node->children('skos', true)->inScheme;
        $inSchemeId = (string) $inScheme->attributes('rdf', true)->resource;
        if ($inSchemeId !== '' && $inSchemeId !== $schemeId) {
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
            if ($allowMissingLabel) {
                return null;
            }
            throw new InvalidCredentialException('Vocabulary concept is missing a language-tagged prefLabel.');
        }
        $notation = (string) $node->children('skos', true)->notation;
        if ($notation === '') {
            $notation = basename(parse_url($id, PHP_URL_PATH) ?: '');
        }

        return new Concept($id, new LocalizedString($labels), new ConceptScheme($schemeId), $notation ?: null);
    }

    private function fetchConcept(string $conceptId, string $schemeId): ?Concept {
        $document = $this->fetcher->fetch($conceptId);
        if (strlen($document) > $this->maxResponseBytes) {
            throw new InvalidCredentialException('Vocabulary concept response exceeds the configured size limit.');
        }
        $xml = $this->parseXml($document);
        $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $xml->registerXPathNamespace('skos', 'http://www.w3.org/2004/02/skos/core#');
        foreach ($xml->xpath('//*[@rdf:about]') ?: [] as $node) {
            if ((string) $node->attributes('rdf', true)->about === $conceptId) {
                return $this->parseConcept($node, $schemeId);
            }
        }

        return null;
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
