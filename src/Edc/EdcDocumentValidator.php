<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanDigitalCredentials\CredentialDocumentValidator;

final class EdcDocumentValidator {

    private readonly CredentialDocumentValidator $validator;

    public function __construct(private readonly EdcProfile $profile = EdcProfile::GENERIC_FULL) {
        $this->validator = new CredentialDocumentValidator($profile->value);
    }

    /** @param array<string, mixed> $document */
    public function validate(array $document): void {
        $this->validator->validate($document);
    }

    public function validateJson(string $json): void {
        $this->validator->validateJson($json);
    }

    public function profile(): EdcProfile {
        return $this->profile;
    }
}
