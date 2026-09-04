<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

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
    ) {
        parent::__construct($id);
        if ($claims === [] || array_filter($claims, static fn ($claim): bool => !$claim instanceof Claim) !== []) {
            throw new InvalidCredentialException('A credential subject requires at least one valid claim.');
        }
    }

    public function toArray(): array
    {
        return [
            'id' => 'urn:epass:person:' . $this->id,
            'type' => 'Person',
            'familyName' => $this->familyName->toArray(),
            'fullName' => $this->fullName->toArray(),
            'givenName' => $this->givenName->toArray(),
            'hasClaim' => array_map(static fn (Claim $claim): array => $claim->toArray(), $this->claims),
        ];
    }
}