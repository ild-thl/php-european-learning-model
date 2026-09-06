<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Vocabulary;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;

final class EscoVocabularySearchProvider implements VocabularySearchProvider {

    public function __construct(
        private readonly VocabularySearchResourceFetcher $fetcher,
        private readonly int $maxResponseBytes = 1_000_000,
    ) {
        if ($maxResponseBytes < 1) {
            throw new \InvalidArgumentException('Vocabulary search response limit must be positive.');
        }
    }

    public function searchConcepts(
        string $schemeId,
        string $query,
        string $language,
        int $limit = 50,
        ?string $cursor = null,
        array $fallbackLanguages = [],
    ): VocabularyConceptPage {
        $type = match ($schemeId) {
            ElmVocabularySchemes::ESCO_SKILLS => 'skill',
            ElmVocabularySchemes::OCCUPATIONS => 'occupation',
            default => throw new InvalidCredentialException('ESCO search does not support this vocabulary scheme.'),
        };
        if ($limit < 1) {
            throw new \InvalidArgumentException('Vocabulary search limits must be positive.');
        }

        try {
            $document = $this->fetcher->search(
                $schemeId,
                $query,
                $language,
                $limit,
                $cursor,
                $fallbackLanguages,
            );
            if (strlen($document) > $this->maxResponseBytes) {
                throw new InvalidCredentialException('Vocabulary search response exceeds the configured size limit.');
            }
            /** @var array<string, mixed> $data */
            $data = json_decode($document, true, 512, JSON_THROW_ON_ERROR);
            if (array_is_list($data)) {
                throw new InvalidCredentialException('ESCO search response must be a JSON object.');
            }
            $embedded = $data['_embedded'] ?? null;
            $rawResults = is_array($embedded) ? ($embedded['results'] ?? null) : null;
            if (!is_array($rawResults)) {
                throw new InvalidCredentialException('ESCO search results must be an array.');
            }

            $scheme = new ConceptScheme($schemeId);
            $concepts = [];
            foreach ($rawResults as $rawResult) {
                if (!is_array($rawResult)) {
                    throw new InvalidCredentialException('ESCO search entries must be objects.');
                }
                $concepts[] = $this->conceptFromResult($rawResult, $scheme, $type, $language, $fallbackLanguages);
            }

            return new VocabularyConceptPage($concepts, $this->nextCursor($data));
        } catch (InvalidCredentialException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new InvalidCredentialException('Unable to search ESCO vocabulary resource.', 0, $exception);
        }
    }

    /**
     * @param array<string, mixed> $result
     * @param list<string> $fallbackLanguages
     */
    private function conceptFromResult(
        array $result,
        ConceptScheme $scheme,
        string $expectedType,
        string $language,
        array $fallbackLanguages,
    ): Concept {
        if (($result['className'] ?? null) !== ucfirst($expectedType)) {
            throw new InvalidCredentialException('ESCO search result has an unexpected resource type.');
        }
        if (!isset($result['uri']) || !is_string($result['uri']) || $result['uri'] === '') {
            throw new InvalidCredentialException('ESCO search result requires a URI.');
        }
        $schemeMembership = $result['isInScheme'] ?? [];
        if (!is_array($schemeMembership) || !in_array($scheme->id, $schemeMembership, true)) {
            throw new InvalidCredentialException('ESCO search result is outside the requested scheme.');
        }
        $labels = $result['preferredLabel'] ?? [];
        if (!is_array($labels)) {
            throw new InvalidCredentialException('ESCO search result labels must be an object.');
        }
        $labels = array_filter($labels, static fn ($value): bool => is_string($value) && $value !== '');
        $normalizedLabels = [];
        foreach ($labels as $tag => $value) {
            $normalizedLabels[$this->normalizeLanguageTag((string) $tag)] = $value;
        }
        $label = new LocalizedString($normalizedLabels);
        if ($label->value($language, $fallbackLanguages) === null) {
            throw new InvalidCredentialException('ESCO search result has no usable preferred label.');
        }

        return new Concept($result['uri'], $label, $scheme);
    }

    private function normalizeLanguageTag(string $tag): string {
        $parts = explode('-', $tag);
        $parts[0] = strtolower($parts[0]);
        foreach ($parts as $index => $part) {
            if ($index === 0) {
                continue;
            }
            $parts[$index] = strlen($part) === 2 || ctype_digit($part)
                ? strtoupper($part)
                : ucfirst(strtolower($part));
        }

        return implode('-', $parts);
    }

    /** @param array<string, mixed> $data */
    private function nextCursor(array $data): ?string {
        $links = $data['_links'] ?? null;
        $next = is_array($links) ? ($links['next'] ?? null) : null;
        $href = is_array($next) ? ($next['href'] ?? null) : null;

        return is_string($href) && $href !== '' ? $href : null;
    }
}
