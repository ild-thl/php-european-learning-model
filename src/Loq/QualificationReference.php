<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanDigitalCredentials\Identifier;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\LegalIdentifier;
use IsyThl\EuropeanLearningModel\Core\JsonLdEncoder;

final class QualificationReference {

    public readonly Identifier|LegalIdentifier $identifier;

    public function __construct(
        Identifier|LegalIdentifier|null $identifier,
        public readonly ?string $datasetNamespace = null,
    ) {
        if ($identifier === null) {
            throw new InvalidCredentialException('A qualification reference requires an identifier.');
        }
        $this->identifier = $identifier;
        if ($datasetNamespace !== null) {
            self::assertUri($datasetNamespace, 'dataset namespace');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'type' => 'QualificationReference',
            'identifier' => $this->identifier->toArray(),
        ];
        if ($this->datasetNamespace !== null) {
            $data['datasetNamespace'] = $this->datasetNamespace;
        }
        return $data;
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
