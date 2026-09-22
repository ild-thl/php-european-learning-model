<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Core\DateTimeFormatter;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ContactPoint;
use IsyThl\EuropeanLearningModel\Core\Identifier;
use IsyThl\EuropeanLearningModel\Core\LegalIdentifier;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Core\Location;
use IsyThl\EuropeanLearningModel\Core\Organisation;
use IsyThl\EuropeanLearningModel\Core\Person;
use IsyThl\EuropeanLearningModel\Edc\Credential;
use IsyThl\EuropeanLearningModel\Edc\Claim;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class CredentialSubject extends Person {

    /**
     * @param list<Claim> $hasClaim
     * @param list<LocalizedString>|null $birthName
     * @param list<LocalizedString>|null $patronymicName
     * @param list<Identifier|LegalIdentifier>|null $identifier
     * @param list<ContactPoint>|null $contactPoint
     * @param list<Organisation>|null $memberOf
     * @param list<Concept>|null $citizenshipCountry
     * @param list<Credential>|null $hasCredential
     * @param list<Concept>|null $hasFamilyRelationship
     */
    public function __construct(
        string $id,
        public readonly array $hasClaim,
        ?LocalizedString $fullName = null,
        ?LocalizedString $familyName = null,
        ?LocalizedString $givenName = null,
        ?array $birthName = null,
        ?array $patronymicName = null,
        ?array $identifier = null,
        ?LegalIdentifier $nationalId = null,
        ?DateTimeImmutable $dateOfBirth = null,
        ?Location $placeOfBirth = null,
        ?Concept $gender = null,
        ?Location $location = null,
        ?array $contactPoint = null,
        ?array $memberOf = null,
        ?array $citizenshipCountry = null,
        ?array $hasCredential = null,
        ?array $hasFamilyRelationship = null,
        ?int $order = null,
        ?DateTimeImmutable $modified = null,
    ) {
        if ($this->hasClaim === [] || array_filter($this->hasClaim, static fn ($claim): bool => !$claim instanceof Claim) !== []) {
            throw new InvalidCredentialException('A credential subject requires at least one valid claim.');
        }
        parent::__construct(
            id: $id,
            fullName: $fullName,
            familyName: $familyName,
            givenName: $givenName,
            birthName: $birthName,
            patronymicName: $patronymicName,
            identifier: $identifier,
            nationalId: $nationalId,
            dateOfBirth: $dateOfBirth,
            placeOfBirth: $placeOfBirth,
            gender: $gender,
            location: $location,
            contactPoint: $contactPoint,
            memberOf: $memberOf,
            citizenshipCountry: $citizenshipCountry,
            hasCredential: $hasCredential,
            hasFamilyRelationship: $hasFamilyRelationship,
            order: $order,
            modified: $modified,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = parent::toArray();
        $data['hasClaim'] = array_map(
            static fn (Claim $claim): array => $claim->toArray(),
            $this->hasClaim,
        );

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['hasClaim']) || !is_array($data['hasClaim'])) {
            throw new InvalidCredentialException('A credential subject requires at least one valid claim.');
        }
        if (!isset($data['id']) || !isset($data['type'])) {
            throw new InvalidCredentialException('A credential subject requires an id and type.');
        }

        return new self(
            $data['id'],
            array_map(static fn (array $claim): Claim => Claim::fromArray($claim), $data['hasClaim']),
            isset($data['fullName']) ? LocalizedString::fromArray($data['fullName']) : null,
            isset($data['familyName']) ? LocalizedString::fromArray($data['familyName']) : null,
            isset($data['givenName']) ? LocalizedString::fromArray($data['givenName']) : null,
            isset($data['birthName']) ? array_map(static fn (array $birthName): LocalizedString => LocalizedString::fromArray($birthName), $data['birthName']) : null,
            isset($data['patronymicName']) ? array_map(static fn (array $patronymicName): LocalizedString => LocalizedString::fromArray($patronymicName), $data['patronymicName']) : null,
            isset($data['identifier']) ? array_map(static fn (array $identifier): Identifier|LegalIdentifier => Identifier::fromArray($identifier), $data['identifier']) : null,
            isset($data['nationalID']) ? LegalIdentifier::fromArray($data['nationalID']) : null,
            isset($data['dateOfBirth']) ? new DateTimeImmutable($data['dateOfBirth']) : null,
            isset($data['placeOfBirth']) ? Location::fromArray($data['placeOfBirth']) : null,
            isset($data['gender']) ? Concept::fromArray($data['gender']) : null,
            isset($data['location']) ? Location::fromArray($data['location']) : null,
            isset($data['contactPoint']) ? array_map(static fn (array $contactPoint): ContactPoint => ContactPoint::fromArray($contactPoint), $data['contactPoint']) : null,
            isset($data['memberOf']) ? array_map(static fn (array $memberOf): Organisation => Organisation::fromArray($memberOf), $data['memberOf']) : null,
            isset($data['citizenshipCountry']) ? array_map(static fn (array $citizenship): Concept => Concept::fromArray($citizenship), $data['citizenshipCountry']) : null,
            isset($data['hasCredential']) ? array_map(static fn (array $hasCredential): Credential => Credential::fromArray($hasCredential), $data['hasCredential']) : null,
            isset($data['hasFamilyRelationship']) ? array_map(static fn (array $hasFamilyRelationship): Concept => Concept::fromArray($hasFamilyRelationship), $data['hasFamilyRelationship']) : null,
            isset($data['order']) ? $data['order'] : null,
            isset($data['modified']) ? new DateTimeImmutable($data['modified']) : null,
        );
    }
}
