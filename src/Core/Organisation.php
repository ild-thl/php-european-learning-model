<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

/**
 * Class Organisation
 *
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#organisation
 */
class Organisation extends Entity {
    public function __construct(
        string $id,
        public readonly LocalizedString $legalName,
        /** @var list<Location> */
        public readonly array $location,
        /** @var list<Identifier|LegalIdentifier>|null */
        public readonly ?array $identifier = null,
        /** @var list<LocalizedString>|null */
        public readonly ?array $altLabel = null,
        public readonly ?LegalIdentifier $eidasLegalIdentifier = null,
        public readonly ?Organisation $subOrganizationOf = null,
        public readonly ?LegalIdentifier $registration = null,
        public readonly ?MediaObject $logo = null,
        /** @var list<Concept>|null */
        public readonly ?array $dcType = null,
        /** @var list<Note>|null */
        public readonly ?array $additionalNote = null,
        /** @var list<WebResource>|null */
        public readonly ?array $homepage = null,
        /** @var list<Organisation>|null */
        public readonly ?array $hasSubOrganization = null,
        /** @var list<LegalIdentifier>|null */
        public readonly ?array $taxIdentifier = null,
        // TODO: groupMemberOf Group
        /** @var list<ContactPoint>|null */
        public readonly ?array $contactPoint = null,
        // TODO: hasMember Person
        /** @var list<LegalIdentifier>|null */
        public readonly ?array $vatIdentifier = null,
        /** @var list<Accreditation>|null */
        public readonly ?array $accreditation = null,
        public readonly ?int $order = null,
        public readonly ?DateTimeImmutable $modified = null,
    ) {
        parent::__construct($id);

        if (empty($this->location)) {
            throw new InvalidCredentialException('Location array must contain at least one Location object.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'Organisation',
            'legalName' => $this->legalName->toArray(),
            'location' => array_map(
                static fn (Location $location): array => $location->toArray(),
                $this->location
            ),
        ];
        if ($this->identifier !== null) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifier
            );
        }
        if ($this->altLabel !== null) {
            $data['altLabel'] = array_map(
                static fn (LocalizedString $altLabel): array => $altLabel->toArray(),
                $this->altLabel,
            );
        }
        if ($this->eidasLegalIdentifier !== null) {
            $data['eidasLegalIdentifier'] = $this->eidasLegalIdentifier->toArray();
        }
        if ($this->subOrganizationOf !== null) {
            $data['subOrganizationOf'] = $this->subOrganizationOf->toArray();
        }
        if ($this->registration !== null) {
            $data['registration'] = $this->registration->toArray();
        }
        if ($this->logo !== null) {
            $data['logo'] = $this->logo->toArray();
        }
        if ($this->dcType !== null) {
            $data['dcType'] = array_map(
                static fn (Concept $concept): array => $concept->toArray(),
                $this->dcType,
            );
        }
        if ($this->additionalNote !== null) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNote,
            );
        }
        if ($this->homepage !== null) {
            $data['homepage'] = array_map(
                static fn (WebResource $webResource): array => $webResource->toArray(),
                $this->homepage,
            );
        }
        if ($this->hasSubOrganization !== null) {
            $data['hasSubOrganization'] = array_map(
                static fn (Organisation $organisation): array => $organisation->toArray(),
                $this->hasSubOrganization,
            );
        }
        if ($this->taxIdentifier !== null) {
            $data['taxIdentifier'] = array_map(
                static fn (LegalIdentifier $legalIdentifier): array => $legalIdentifier->toArray(),
                $this->taxIdentifier,
            );
        }
        if ($this->contactPoint !== null) {
            $data['contactPoint'] = array_map(
                static fn (ContactPoint $contactPoint): array => $contactPoint->toArray(),
                $this->contactPoint,
            );
        }
        if ($this->vatIdentifier !== null) {
            $data['vatIdentifier'] = array_map(
                static fn (LegalIdentifier $legalIdentifier): array => $legalIdentifier->toArray(),
                $this->vatIdentifier,
            );
        }
        if ($this->accreditation !== null) {
            $data['accreditation'] = array_map(
                static fn (Accreditation $accreditation): array => $accreditation->toArray(),
                $this->accreditation,
            );
        }
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }
        if ($this->modified !== null) {
            $data['modified'] = DateTimeFormatter::format($this->modified);
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Organisation') {
            throw new InvalidCredentialException('Data is not an Organisation.');
        }
        if (!isset($data['legalName'])) {
            throw new InvalidCredentialException('Data is missing legalName.');
        }
        if (!isset($data['location']) || !is_array($data['location']) || empty($data['location'])) {
            throw new InvalidCredentialException('Data must have at least one location.');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['legalName']),
            array_map(static fn (array $location): Location => Location::fromArray($location), $data['location']),
            isset($data['identifier']) ? array_map(
                static fn (array $identifier): Identifier|LegalIdentifier => Identifier::fromArray($identifier),
                $data['identifier']
            ) : null,
            isset($data['altLabel']) ? array_map(
                static fn (array $altLabel): LocalizedString => LocalizedString::fromArray($altLabel),
                $data['altLabel']
            ) : null,
            isset($data['eidasLegalIdentifier']) ? LegalIdentifier::fromArray($data['eidasLegalIdentifier']) : null,
            isset($data['subOrganizationOf']) ? Organisation::fromArray($data['subOrganizationOf']) : null,
            isset($data['registration']) ? LegalIdentifier::fromArray($data['registration']) : null,
            isset($data['logo']) ? MediaObject::fromArray($data['logo']) : null,
            isset($data['dcType']) ? array_map(
                static fn (array $concept): Concept => Concept::fromArray($concept),
                $data['dcType']
            ) : null,
            isset($data['additionalNote']) ? array_map(
                static fn (array $note): Note => Note::fromArray($note),
                $data['additionalNote']
            ) : null,
            isset($data['homepage']) ? array_map(
                static fn (array $webResource): WebResource => WebResource::fromArray($webResource),
                $data['homepage']
            ) : null,
            isset($data['hasSubOrganization']) ? array_map(
                static fn (array $organisation): Organisation => Organisation::fromArray($organisation),
                $data['hasSubOrganization']
            ) : null,
            isset($data['taxIdentifier']) ? array_map(
                static fn (array $legalIdentifier): LegalIdentifier => LegalIdentifier::fromArray($legalIdentifier),
                $data['taxIdentifier']
            ) : null,
            isset($data['contactPoint']) ? array_map(
                static fn (array $contactPoint): ContactPoint => ContactPoint::fromArray($contactPoint),
                $data['contactPoint']
            ) : null,
            isset($data['vatIdentifier']) ? array_map(
                static fn (array $legalIdentifier): LegalIdentifier => LegalIdentifier::fromArray($legalIdentifier),
                $data['vatIdentifier']
            ) : null,
            isset($data['accreditation']) ? array_map(
                static fn (array $accreditation): Accreditation => Accreditation::fromArray($accreditation),
                $data['accreditation']
            ) : null,
            isset($data['order']) ? $data['order'] : null,
            isset($data['modified']) ? new DateTimeImmutable($data['modified']) : null,
        );
    }
}
