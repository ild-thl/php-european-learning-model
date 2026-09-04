<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class DisplayDetail extends Entity
{
    public function __construct(
        string $id,
        public readonly int $page,
        public readonly MediaObject $image,
    ) {
        parent::__construct($id);
        if ($page < 1) {
            throw new InvalidCredentialException('A display detail page must be positive.');
        }
    }

    public function toArray(): array
    {
        return [
            'id' => 'urn:epass:displayDetail:' . $this->id,
            'type' => 'DisplayDetail',
            'image' => $this->image->toArray(),
            'page' => $this->page,
        ];
    }
}