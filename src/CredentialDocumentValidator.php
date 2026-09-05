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
            'id',
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
        if (!is_string($document['id']) || $document['id'] === '') {
            throw new InvalidCredentialException('Credential document requires a non-empty id.');
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
        $this->assertEntityShape($document['credentialSchema'], 'credentialSchema', 'ShaclValidator2017');
        $this->assertEntityShape($document['credentialSubject'], 'credentialSubject', 'Person');
        $this->assertEntityShape($document['displayParameter'], 'displayParameter', 'DisplayParameter');
        $this->assertRequiredFields(
            $document['credentialSubject'],
            'credentialSubject',
            ['givenName', 'familyName', 'fullName', 'hasClaim'],
        );
        if (
            !is_array($document['credentialSubject']['hasClaim'])
            || $document['credentialSubject']['hasClaim'] === []
        ) {
            throw new InvalidCredentialException('Credential subject must contain at least one claim.');
        }
        foreach ($document['credentialSubject']['hasClaim'] as $claim) {
            if (!is_array($claim) || array_is_list($claim)) {
                throw new InvalidCredentialException('Credential subject claims must be objects.');
            }
            $this->assertEntityReference($claim, 'credentialSubject.hasClaim');
        }
        $this->assertRequiredFields(
            $document['displayParameter'],
            'displayParameter',
            ['language', 'primaryLanguage', 'title'],
        );
        if (array_key_exists('issuer', $document)) {
            if (!is_array($document['issuer']) || array_is_list($document['issuer'])) {
                throw new InvalidCredentialException('Credential field "issuer" has an invalid shape.');
            }
            $this->assertEntityShape($document['issuer'], 'issuer', 'Organisation');
        }
        if ($document['credentialSchema']['id'] !== 'http://data.europa.eu/snb/model/ap/edc-generic-full') {
            throw new InvalidCredentialException('Credential schema has an unsupported identifier.');
        }
        if ($document['credentialProfiles'] === []) {
            throw new InvalidCredentialException('Credential profiles must contain at least one profile.');
        }
        foreach ($document['credentialProfiles'] as $profile) {
            if (!is_array($profile) || array_is_list($profile)) {
                throw new InvalidCredentialException('Credential profiles must contain concept objects.');
            }
            if (($profile['type'] ?? null) !== 'Concept') {
                throw new InvalidCredentialException('Credential profiles must be Concept objects.');
            }
            if (
                !is_string($profile['id'] ?? null)
                || $profile['id'] === ''
                || !is_array($profile['prefLabel'] ?? null)
                || $profile['prefLabel'] === []
            ) {
                throw new InvalidCredentialException(
                    'Credential profiles must contain id and prefLabel fields.',
                );
            }
            $this->assertLocalizedLanguageMap($profile['prefLabel'], 'credentialProfiles.prefLabel');
            $profileScheme = $profile['inScheme'] ?? null;
            if (
                !is_array($profileScheme)
                || ($profileScheme['id'] ?? null) !== ElmVocabularySchemes::CREDENTIAL
            ) {
                throw new InvalidCredentialException('Credential profiles must use the ELM credential profile scheme.');
            }
        }
        if (!$this->isUtcDate($document['validFrom'])) {
            throw new InvalidCredentialException('Credential validFrom must be a UTC date in ELM format.');
        }
        foreach (['givenName', 'familyName', 'fullName'] as $field) {
            $this->assertLocalizedLanguageMap($document['credentialSubject'][$field], 'credentialSubject.' . $field);
        }
        $this->assertLocalizedLanguageMap($document['displayParameter']['title'], 'displayParameter.title');
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

    /** @param array<string, mixed> $entity */
    private function assertEntityShape(array $entity, string $field, string $type): void {
        if (!is_string($entity['id'] ?? null) || $entity['id'] === '' || ($entity['type'] ?? null) !== $type) {
            throw new InvalidCredentialException(
                sprintf('Credential field "%s" must be a %s object with an id.', $field, $type),
            );
        }
    }

    /**
     * @param array<string, mixed> $entity
     * @param list<string> $fields
     */
    private function assertRequiredFields(array $entity, string $entityName, array $fields): void {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $entity) || !is_array($entity[$field]) || $entity[$field] === []) {
                throw new InvalidCredentialException(
                    sprintf('Credential %s is missing required field "%s".', $entityName, $field),
                );
            }
        }
    }

    /** @param array<string, mixed> $entity */
    private function assertEntityReference(array $entity, string $field): void {
        if (
            !is_string($entity['id'] ?? null)
            || $entity['id'] === ''
            || !is_string($entity['type'] ?? null)
            || $entity['type'] === ''
        ) {
            throw new InvalidCredentialException(sprintf('Credential field "%s" must contain id and type.', $field));
        }
    }

    /** @param mixed $value */
    private function assertLocalizedLanguageMap(mixed $value, string $field): void {
        if (!is_array($value) || $value === [] || array_is_list($value)) {
            throw new InvalidCredentialException(sprintf('Credential field "%s" must be a language map.', $field));
        }

        foreach ($value as $language => $translations) {
            if (
                !is_string($language)
                || preg_match('/^[a-z]{2,3}(?:-[A-Z][a-z]{3})?(?:-[A-Z]{2}|-[0-9]{3})?$/', $language) !== 1
                || !is_array($translations)
                || $translations === []
                || !array_is_list($translations)
                || array_filter(
                    $translations,
                    static fn (mixed $translation): bool => !is_string($translation) || $translation === '',
                ) !== []
            ) {
                throw new InvalidCredentialException(
                    sprintf('Credential field "%s" must contain non-empty localized string arrays.', $field),
                );
            }
        }
    }
}
