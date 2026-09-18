<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanLearningModel\Core\Identifier;
use IsyThl\EuropeanLearningModel\Core\LegalIdentifier;
use IsyThl\EuropeanLearningModel\Core\ContactPoint;
use IsyThl\EuropeanLearningModel\Core\LearningActivity;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Edc\Claim;
use DateTimeImmutable;
use DateTimeZone;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class CredentialSubject extends \IsyThl\EuropeanLearningModel\Core\Entity {

    /** @param list<Claim|LearningActivity> $claims */
    public function __construct(
        string $id,
        public readonly LocalizedString $givenName,
        public readonly LocalizedString $familyName,
        public readonly LocalizedString $fullName,
        /** @var list<Claim> */
        public readonly array $claims,
        public readonly ?LocalizedString $birthName = null,
        public readonly ?DateTimeImmutable $dateOfBirth = null,
        public readonly ?Identifier $identifier = null,
        public readonly ?LegalIdentifier $nationalId = null,
        public readonly ?ContactPoint $contactPoint = null,
    ) {
        parent::__construct($id);
        if (
            $claims === []
            || array_filter(
                $claims,
                static fn ($claim): bool => !$claim instanceof Claim,
            )
            !== []
        ) {
            throw new InvalidCredentialException('A credential subject requires at least one valid claim.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:person:' . $this->id,
            'type' => 'Person',
            'familyName' => $this->familyName->toArray(),
            'fullName' => $this->fullName->toArray(),
            'givenName' => $this->givenName->toArray(),
            'hasClaim' => array_map(
                static fn (Claim|LearningActivity $claim): array => $claim->toArray(),
                $this->claims,
            ),
        ];

        if ($this->dateOfBirth !== null) {
            $data['dateOfBirth'] = $this->dateOfBirth->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\\TH:i:s\\Z');
        }
        if ($this->contactPoint !== null) {
            $data['contactPoint'] = [$this->contactPoint->toArray()];
        }
        if ($this->identifier !== null) {
            $data['identifier'] = [$this->identifier->toArray()];
        }
        if ($this->nationalId !== null) {
            $data['nationalID'] = $this->nationalId->toArray();
        }
        if ($this->birthName !== null) {
            $data['birthName'] = $this->birthName->toArray();
        }

        return $data;
    }
}
