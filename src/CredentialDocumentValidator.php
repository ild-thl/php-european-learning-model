<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use DateTimeImmutable;
use DateTimeZone;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class CredentialDocumentValidator {
    /** @param array<string, mixed> $document */
    public function validate(array $document): void {
        if (array_is_list($document)) {
            throw new InvalidCredentialException('A credential document must be a JSON object.');
        }

        $requiredFields = [
            '@context',
            'type',
            'credentialProfiles',
            'credentialSchema',
            'credentialSubject',
            'displayParameter',
            'validFrom',
        ];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $document)) {
                throw new InvalidCredentialException(
                    sprintf('Credential document is missing required field "%s".', $field),
                );
            }
        }

        $expectedContext = [
            'https://www.w3.org/2018/credentials/v1',
            'http://data.europa.eu/snb/model/context/edc-ap',
        ];
        if ($document['@context'] !== $expectedContext) {
            throw new InvalidCredentialException('Credential document has an unsupported JSON-LD context.');
        }
        $expectedType = ['VerifiableCredential', 'EuropeanDigitalCredential'];
        if ($document['type'] !== $expectedType) {
            throw new InvalidCredentialException('Credential document has an unsupported type.');
        }
        $objectFields = ['credentialProfiles', 'credentialSchema', 'credentialSubject', 'displayParameter'];
        foreach ($objectFields as $field) {
            if (!is_array($document[$field])) {
                throw new InvalidCredentialException(sprintf('Credential field "%s" has an invalid shape.', $field));
            }
            if ($field !== 'credentialProfiles' && array_is_list($document[$field])) {
                throw new InvalidCredentialException(sprintf('Credential field "%s" has an invalid shape.', $field));
            }
        }
        if ($document['credentialProfiles'] === []) {
            throw new InvalidCredentialException('Credential profiles must contain at least one profile.');
        }
        if (!$this->isUtcDate($document['validFrom'])) {
            throw new InvalidCredentialException('Credential validFrom must be a UTC date in ELM format.');
        }
        foreach (['expirationDate', 'issuanceDate', 'issued', 'validUntil'] as $field) {
            if (array_key_exists($field, $document) && !$this->isUtcDate($document[$field])) {
                throw new InvalidCredentialException(
                    sprintf('Credential %s must be a UTC date in ELM format.', $field),
                );
            }
        }
    }

    public function validateJson(string $json): void {
        try {
            $document = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidCredentialException('Credential JSON is invalid.', 0, $exception);
        }
        if (!is_array($document)) {
            throw new InvalidCredentialException('A credential document must be a JSON object.');
        }
        $this->validate($document);
    }

    private function isUtcDate(mixed $value): bool {
        if (!is_string($value)) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d\TH:i:s\Z',
            $value,
            new DateTimeZone('UTC'),
        );

        return $date !== false && $date->format('Y-m-d\TH:i:s\Z') === $value;
    }
}
