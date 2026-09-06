<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Core\Validation\ProfileResourceRegistryInterface;
use IsyThl\EuropeanLearningModel\Core\Validation\StandardsValidationResult;
use IsyThl\EuropeanLearningModel\Core\Validation\StandardsValidatorInterface;

final class LoqDocumentValidator {

    public function __construct(
        private readonly StandardsValidatorInterface $standardsValidator,
        private readonly ProfileResourceRegistryInterface $profileResources,
    ) {
    }

    public function validate(
        LearningOpportunityDocument|QualificationDocument $document,
        string $profileResource,
    ): void {
        $result = $this->standardsValidator->validate(
            $document->toJson(),
            $this->profileResources->get($profileResource),
        );
        if (!$result->valid) {
            throw new InvalidCredentialException(self::formatErrors($result));
        }
    }

    private static function formatErrors(StandardsValidationResult $result): string {
        if ($result->errors === []) {
            return 'LOQ standards validation failed without a diagnostic.';
        }

        return 'LOQ standards validation failed: ' . implode('; ', $result->errors);
    }
}
