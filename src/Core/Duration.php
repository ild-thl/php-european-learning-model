<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Duration {
    private const PATTERN = '/^P(?:\d+Y)?(?:\d+M)?(?:\d+D)?(?:T(?=\d)(?:\d+H)?(?:\d+M)?(?:\d+(?:\.\d+)?S)?)?$/';

    private function __construct(public readonly string $value) {
    }

    public static function fromString(string $value): self {
        if (preg_match(self::PATTERN, $value) !== 1) {
            throw new InvalidCredentialException('Duration must use ISO 8601 format.');
        }

        return new self($value);
    }

    public function __toString(): string {
        return $this->value;
    }
}
