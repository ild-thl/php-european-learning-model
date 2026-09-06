<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Vocabulary;

interface VocabularySearchProvider {

    /**
     * Search a scheme without requiring the complete vocabulary in memory.
     *
     * The cursor is opaque and must be passed back unchanged by callers.
     *
     * @param list<string> $fallbackLanguages
     */
    public function searchConcepts(
        string $schemeId,
        string $query,
        string $language,
        int $limit = 50,
        ?string $cursor = null,
        array $fallbackLanguages = [],
    ): VocabularyConceptPage;
}
