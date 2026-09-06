<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Vocabulary;

use IsyThl\EuropeanLearningModel\Concept;

final class VocabularyConceptPage {

    /**
     * @param list<Concept> $concepts
     */
    public function __construct(
        public readonly array $concepts,
        public readonly ?string $nextCursor = null,
    ) {
        if (array_filter($concepts, static fn ($concept): bool => !$concept instanceof Concept) !== []) {
            throw new \InvalidArgumentException('Vocabulary pages accept Concept objects.');
        }
        if ($nextCursor === '') {
            throw new \InvalidArgumentException('Vocabulary page cursors must not be empty.');
        }
    }

    public function hasMore(): bool {
        return $this->nextCursor !== null;
    }
}
