<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class Identifier extends Entity {

    public function __construct(
        string $id,
        public readonly string $notation,
        public readonly string $schemeName,
    ) {
        parent::__construct($id);
        if ($notation === '' || $schemeName === '') {
            throw new InvalidCredentialException('An identifier requires notation and scheme name.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => 'urn:epass:identifier:' . $this->id,
            'type' => 'Identifier',
            'notation' => $this->notation,
            'schemeName' => $this->schemeName,
        ];
    }
}
