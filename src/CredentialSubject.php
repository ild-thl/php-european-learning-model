<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use DateTimeImmutable;
use DateTimeZone;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class CredentialSubject extends Entity
{
    /** @param list<Claim> $claims */
    public function __construct(
        string $id,
        public readonly LocalizedString $givenName,
        public readonly LocalizedString $familyName,
        public readonly LocalizedString $fullName,
        public readonly array $claims,
        public readonly ?LocalizedString $birthName = null,
        public readonly ?DateTimeImmutable $dateOfBirth = null,
    ) {
        parent::__construct($id);
        if ($claims === [] || array_filter($claims, static fn ($claim): bool => !$claim instanceof Claim) !== []) {
            throw new InvalidCredentialException('A credential subject requires at least one valid claim.');
        }
    }

    public function toArray(): array
    {
        $data = [
            'id' => 'urn:epass:person:' . $this->id,
            'type' => 'Person',
            'familyName' => $this->familyName->toArray(),
            'fullName' => $this->fullName->toArray(),
            'givenName' => $this->givenName->toArray(),
            'hasClaim' => array_map(static fn (Claim $claim): array => $claim->toArray(), $this->claims),
        ];

        if ($this->dateOfBirth !== null) {
            $data['dateOfBirth'] = $this->dateOfBirth->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\\TH:i:s\\Z');
        }
        if ($this->birthName !== null) {
            $data['birthName'] = $this->birthName->toArray();
        }

        return $data;
    }
}