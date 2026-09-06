<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class JsonLdVocabularyProvider implements VocabularyProvider {

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
            /** @var array<string, mixed> $data */
            $data = json_decode($document, true, 512, JSON_THROW_ON_ERROR);
            return $this->parseScheme($schemeId, $data);
        } catch (InvalidCredentialException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new InvalidCredentialException('Unable to load vocabulary resource.', 0, $exception);
        }
    }

    public function getConcept(string $conceptId, string $schemeId): ?Concept {
        return $this->getScheme($schemeId)?->find($conceptId);
    }

    public function getConceptByNotation(string $notation, string $schemeId): ?Concept {
        return $this->getScheme($schemeId)?->findByNotation($notation);
    }

    /** @param array<string, mixed> $data */
    private function parseScheme(string $schemeId, array $data): ?VocabularyScheme {
        $nodes = $data['@graph'] ?? $data['graph'] ?? [];
        if (!is_array($nodes)) {
            throw new InvalidCredentialException('Vocabulary JSON-LD graph must be an array.');
        }
        $concepts = [];
        foreach ($nodes as $node) {
            if (!is_array($node) || !$this->isConceptNode($node)) {
                continue;
            }
            $concepts[] = $this->parseConcept($node, $schemeId);
        }
        $schemeData = $this->findNode($nodes, $schemeId);
        if ($schemeData === null && $concepts === []) {
            return null;
        }
        $schemeType = is_array($schemeData) && is_string($schemeData['type'] ?? null)
            ? $schemeData['type']
            : 'ConceptScheme';

        return new VocabularyScheme(
            $schemeId,
            new ConceptScheme($schemeId, $schemeType),
            $concepts,
            $this->parseLocalized($schemeData['prefLabel'] ?? null),
            $schemeId,
        );
    }

    /** @param array<string, mixed> $node */
    private function isConceptNode(array $node): bool {
        $type = $node['type'] ?? $node['@type'] ?? null;
        return $type === 'Concept' || (is_array($type) && in_array('Concept', $type, true));
    }

    /** @param array<string, mixed> $node */
    private function parseConcept(array $node, string $schemeId): Concept {
        $id = $node['id'] ?? $node['@id'] ?? null;
        $inScheme = $node['inScheme'] ?? null;
        $inSchemeId = is_array($inScheme) ? ($inScheme['id'] ?? $inScheme['@id'] ?? null) : $inScheme;
        if (!is_string($id) || $inSchemeId !== $schemeId) {
            throw new InvalidCredentialException('Vocabulary concept has an invalid identifier or scheme.');
        }
        $label = $this->parseLocalized($node['prefLabel'] ?? null);
        if ($label === null) {
            throw new InvalidCredentialException('Vocabulary concept is missing a prefLabel.');
        }

        return new Concept($id, $label, new ConceptScheme($schemeId), $node['notation'] ?? null);
    }

    /**
     * @param list<mixed> $nodes
     * @return array<string, mixed>|null
     */
    private function findNode(array $nodes, string $id): ?array {
        foreach ($nodes as $node) {
            if (is_array($node) && (($node['id'] ?? $node['@id'] ?? null) === $id)) {
                return $node;
            }
        }

        return null;
    }

    private function parseLocalized(mixed $value): ?LocalizedString {
        if (!is_array($value) || $value === []) {
            return null;
        }
        $translations = [];
        foreach ($value as $language => $labels) {
            if (!is_string($language) || !is_string($labels) && !is_array($labels)) {
                throw new InvalidCredentialException('Vocabulary labels must be language maps.');
            }
            $translations[$language] = $labels;
        }

        return new LocalizedString($translations);
    }
}
