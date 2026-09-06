<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Vocabulary;

interface VocabularySearchResourceFetcher {

    /**
     * @param list<string> $fallbackLanguages
     * @throws \Throwable when the resource cannot be retrieved
     */
    public function search(
        string $schemeId,
        string $query,
        string $language,
        int $limit,
        ?string $cursor,
        array $fallbackLanguages = [],
    ): string;
}
