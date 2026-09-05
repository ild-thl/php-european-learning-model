<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Vocabulary;

use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptAssertions;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class JsonVocabularySearchProvider implements VocabularySearchProvider {

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
                throw new InvalidCredentialException('Vocabulary search response must be a JSON object.');
            }
            $rawConcepts = $data['concepts'] ?? [];
            if (!is_array($rawConcepts)) {
                throw new InvalidCredentialException('Vocabulary search concepts must be an array.');
            }
            $concepts = [];
            foreach ($rawConcepts as $rawConcept) {
                if (!is_array($rawConcept)) {
                    throw new InvalidCredentialException('Vocabulary search entries must be objects.');
                }
                $concept = Concept::fromArray($rawConcept);
                ConceptAssertions::assertScheme($concept, $schemeId, 'vocabulary search concept');
                $concepts[] = $concept;
            }
            $nextCursor = $data['nextCursor'] ?? null;
            if ($nextCursor !== null && !is_string($nextCursor)) {
                throw new InvalidCredentialException('Vocabulary search nextCursor must be a string or null.');
            }

            return new VocabularyConceptPage($concepts, $nextCursor);
        } catch (InvalidCredentialException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new InvalidCredentialException('Unable to search vocabulary resource.', 0, $exception);
        }
    }
}
