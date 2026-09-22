<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Location extends Entity {

    public function __construct(
        string $id,
        /** @var list<Address> */
        public readonly array $address,
    ) {
        parent::__construct($id);

        if (empty($this->address)) {
            throw new InvalidCredentialException('Location must have at least one address.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'type' => 'Location',
            'address' => array_map(static fn (Address $address): array => $address->toArray(), $this->address),
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Location') {
            throw new InvalidCredentialException('Data is not a Location.');
        }

        return new self(
            $data['id'],
            isset($data['address']) && is_array($data['address'])
                ? array_map(static fn (array $address): Address => Address::fromArray($address), $data['address'])
                : [],
        );
    }
}
