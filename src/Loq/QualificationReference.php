<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Core\JsonLdEncoder;

final class QualificationReference {

    public readonly string $identifier;

    public function __construct(
        string $identifier,
        public readonly string $datasetNamespace,
    ) {
        self::assertUri($identifier, 'qualification identifier');
        self::assertUri($datasetNamespace, 'dataset namespace');
        $this->identifier = $identifier;
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'type' => 'QualificationReference',
            'id' => $this->identifier,
            'datasetNamespace' => $this->datasetNamespace,
        ];
    }

    public function toJson(): string {
        return JsonLdEncoder::encode($this->toArray());
    }

    private static function assertUri(string $value, string $field): void {
        if ($value === '' || preg_match('/^[a-z][a-z0-9+.-]*:\S+$/i', $value) !== 1) {
            throw new InvalidCredentialException(sprintf('The %s must be a valid persistent URI.', $field));
        }
    }
}
