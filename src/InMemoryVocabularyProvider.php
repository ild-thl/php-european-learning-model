<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Vocabulary\VocabularyConceptPage;
use IsyThl\EuropeanLearningModel\Vocabulary\VocabularySearchProvider;

final class InMemoryVocabularyProvider implements VocabularyProvider, VocabularySearchProvider {

    /** @var array<string, VocabularyScheme> */
    private array $schemes = [];

    /**
     * @param list<VocabularyScheme> $schemes
     */
    public function __construct(array $schemes = []) {
        foreach ($schemes as $scheme) {
            if (!$scheme instanceof VocabularyScheme) {
                throw new InvalidCredentialException('Vocabulary providers accept VocabularyScheme objects.');
            }
            if (isset($this->schemes[$scheme->id])) {
                throw new InvalidCredentialException('A vocabulary provider cannot contain duplicate schemes.');
            }
            $this->schemes[$scheme->id] = $scheme;
        }
    }

    public function getScheme(string $schemeId): ?VocabularyScheme {
        return $this->schemes[$schemeId] ?? null;
    }

    public function getConcept(string $conceptId, string $schemeId): ?Concept {
        return $this->schemes[$schemeId]?->find($conceptId);
    }

    public function getConceptByNotation(string $notation, string $schemeId): ?Concept {
        return $this->schemes[$schemeId]?->findByNotation($notation);
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
        $scheme = $this->schemes[$schemeId] ?? null;
        if ($scheme === null) {
            return new VocabularyConceptPage([]);
        }
        $matches = $scheme->search($query, $language, $fallbackLanguages);
        $offset = $cursor === null ? 0 : filter_var($cursor, FILTER_VALIDATE_INT);
        if ($offset === false || $offset < 0) {
            throw new \InvalidArgumentException('Vocabulary search cursors must be non-negative offsets.');
        }
        $page = array_slice($matches, $offset, $limit);
        $nextCursor = count($matches) > $offset + count($page) ? (string) ($offset + count($page)) : null;

        return new VocabularyConceptPage($page, $nextCursor);
    }
}
