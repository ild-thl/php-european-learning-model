<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class Location extends Entity
{
    public function __construct(string $id, public readonly Address $address)
    {
        parent::__construct($id);
    }

    public function toArray(): array
    {
        return [
            'id' => 'urn:epass:location:' . $this->id,
            'type' => 'Location',
            'address' => $this->address->toArray(),
        ];
    }
}