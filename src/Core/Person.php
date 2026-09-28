<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Edc\Credential;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

/**
 * Class Person
 *
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#person
 */
class Person extends Entity {
    public function __construct(
        string $id,
        public readonly ?LocalizedString $fullName = null,
        public readonly ?LocalizedString $familyName = null,
        public readonly ?LocalizedString $givenName = null,
        /** @var list<LocalizedString>|null */
        public readonly ?array $birthName = null,
        /** @var list<LocalizedString>|null */
        public readonly ?array $patronymicName = null,
        /** @var list<Identifier|LegalIdentifier>|null */
        public readonly ?array $identifier = null,
        public readonly ?LegalIdentifier $nationalId = null,
        public readonly ?DateTimeImmutable $dateOfBirth = null,
        public readonly ?Location $placeOfBirth = null,
        public readonly ?Concept $gender = null,
        public readonly ?Location $location = null,
        /** @var list<ContactPoint>|null */
        public readonly ?array $contactPoint = null,
        // TODO: groupMemberOf Group
        /** @var list<Organisation>|null */
        public readonly ?array $memberOf = null,
        /** @var list<Concept>|null */
        public readonly ?array $citizenshipCountry = null,
        /** @var list<Credential>|null */
        public readonly ?array $hasCredential = null,
        /** @var list<Concept>|null */
        public readonly ?array $hasFamilyRelationship = null,
        public readonly ?int $order = null,
        public readonly ?DateTimeImmutable $modified = null,
    ) {
        parent::__construct($id);

        if ($this->citizenshipCountry !== null) {
            foreach ($this->citizenshipCountry as $country) {
                ConceptAssertions::assertScheme($country, ElmVocabularySchemes::COUNTRY, 'citizenshipCountry');
            }
        }
        if ($this->hasFamilyRelationship !== null) {
            foreach ($this->hasFamilyRelationship as $familyRelationship) {
                ConceptAssertions::assertScheme($familyRelationship, ElmVocabularySchemes::FAMILY_RELATIONSHIP, 'hasFamilyRelationship');
            }
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $this->beginSerialization();

        try {
            $data = [
                'id' => $this->id,
                'type' => 'Person',
            ];
            if ($this->fullName !== null) {
                $data['fullName'] = $this->fullName->toArray();
            }
            if ($this->familyName !== null) {
                $data['familyName'] = $this->familyName->toArray();
            }
            if ($this->givenName !== null) {
                $data['givenName'] = $this->givenName->toArray();
            }
            if ($this->birthName !== null) {
                $data['birthName'] = array_map(static fn (LocalizedString $name): array => $name->toArray(), $this->birthName);
            }
            if ($this->patronymicName !== null) {
                $data['patronymicName'] = array_map(static fn (LocalizedString $name): array => $name->toArray(), $this->patronymicName);
            }
            if ($this->identifier !== null) {
                $data['identifier'] = array_map(static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(), $this->identifier);
            }
            if ($this->nationalId !== null) {
                $data['nationalID'] = $this->nationalId->toArray();
            }
            if ($this->dateOfBirth !== null) {
                $data['dateOfBirth'] = DateTimeFormatter::format($this->dateOfBirth);
            }
            if ($this->placeOfBirth !== null) {
                $data['placeOfBirth'] = $this->placeOfBirth->toArray();
            }
            if ($this->gender !== null) {
                $data['gender'] = $this->gender->toArray();
            }
            if ($this->location !== null) {
                $data['location'] = $this->location->toArray();
            }
            if ($this->contactPoint !== null) {
                $data['contactPoint'] = array_map(static fn (ContactPoint $contactPoint): array => $contactPoint->toArray(), $this->contactPoint);
            }
            if ($this->memberOf !== null) {
                $data['memberOf'] = array_map(static fn (Organisation $organisation): array => $organisation->toArray(), $this->memberOf);
            }
            if ($this->citizenshipCountry !== null) {
                $data['citizenshipCountry'] = array_map(static fn (Concept $country): array => $country->toArray(), $this->citizenshipCountry);
            }
            if ($this->hasCredential !== null) {
                $data['hasCredential'] = array_map(static fn (Credential $credential): array => $credential->toArray(), $this->hasCredential);
            }
            if ($this->hasFamilyRelationship !== null) {
                $data['hasFamilyRelationship'] = array_map(static fn (Concept $familyRelationship): array => $familyRelationship->toArray(), $this->hasFamilyRelationship);
            }
            if ($this->order !== null) {
                $data['order'] = $this->order;
            }
            if ($this->modified !== null) {
                $data['modified'] = DateTimeFormatter::format($this->modified);
            }

            return $data;
        } finally {
            $this->endSerialization();
        }
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Person') {
            throw new InvalidCredentialException('Data is not an Person.');
        }

        return new self(
            $data['id'],
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
