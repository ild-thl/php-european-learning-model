<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class Address extends Entity {

    public function __construct(
        string $id,
        public readonly Concept $countryCode,
        public readonly Note $fullAddress,
    ) {
        parent::__construct($id);
    }

    public function toArray(): array {
        return [
            'id' => 'urn:epass:address:' . $this->id,
            'type' => 'Address',
            'countryCode' => $this->countryCode->toArray(),
            'fullAddress' => $this->fullAddress->toArray(),
        ];
    }
}
