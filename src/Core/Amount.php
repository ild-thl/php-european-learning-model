<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Concept;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Amount {

    public function __construct(
        public readonly string $value,
        public readonly Concept $unit,
    ) {
        if (preg_match('/^[+-]?(?:\d+(?:\.\d*)?|\.\d+)$/', $value) !== 1) {
            throw new InvalidCredentialException('Amount values must use XML Schema decimal syntax.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'type' => 'Amount',
            'value' => $this->value,
            'unit' => $this->unit->toArray(),
        ];
    }
}
