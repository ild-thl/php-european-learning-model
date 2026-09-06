<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

interface VocabularyResourceFetcher {

    /** @throws \Throwable when the resource cannot be retrieved */
    public function fetch(string $resource): string;
}
