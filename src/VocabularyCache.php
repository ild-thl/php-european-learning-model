<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\VocabularyScheme;

interface VocabularyCache {

    public function get(string $key): ?VocabularyScheme;

    public function set(string $key, VocabularyScheme $scheme, int $ttlSeconds): void;
}
