<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

abstract class Claim extends \IsyThl\EuropeanLearningModel\Core\Entity {

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type'])) {
            throw new InvalidCredentialException('Claim data is missing a type.');
        }

        return match ($data['type']) {
            'LearningAchievement' => LearningAchievement::fromArray($data),
            'LearningAssessment' => LearningAssessment::fromArray($data),
            'LearningEntitlement' => LearningEntitlement::fromArray($data),
            default => throw new InvalidCredentialException(
                sprintf('Unsupported claim type: %s.', $data['type']),
            ),
        };
    }
}
