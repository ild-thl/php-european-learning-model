<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class ConceptAssertions {
    public static function assertScheme(Concept $concept, string $schemeId, string $field): void {
        if ($concept->inScheme !== null && $concept->inScheme->id !== $schemeId) {
            throw new InvalidCredentialException(
                sprintf('%s must use vocabulary scheme "%s".', $field, $schemeId),
            );
        }
    }

    public static function assertSchemePrefix(Concept $concept, string $schemePrefix, string $field): void {
        if ($concept->inScheme !== null && !str_starts_with($concept->inScheme->id, $schemePrefix)) {
            throw new InvalidCredentialException(
                sprintf('%s must use a vocabulary scheme under "%s".', $field, $schemePrefix),
            );
        }
    }

    /** @param list<Concept> $concepts */
    public static function assertSchemes(array $concepts, string $schemeId, string $field): void {
        foreach ($concepts as $concept) {
            if (!$concept instanceof Concept) {
                throw new InvalidCredentialException(sprintf('%s must contain concepts.', $field));
            }
            self::assertScheme($concept, $schemeId, $field);
        }
    }
}
