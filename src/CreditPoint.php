<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class CreditPoint extends Entity
{
    public function __construct(
        string $id,
        public readonly Concept $framework,
        public readonly string $point,
    ) {
        parent::__construct($id);
        if ($point === '') {
            throw new InvalidCredentialException('A credit point requires a point value.');
        }
    }

    public function toArray(): array
    {
        return [
            'id' => 'urn:epass:creditPoint:' . $this->id,
            'type' => 'CreditPoint',
            'framework' => $this->framework->toArray(),
            'point' => $this->point,
        ];
    }
}