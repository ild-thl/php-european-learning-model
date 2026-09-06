<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\Address;

final class Location extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(string $id, public readonly Address $address) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => 'urn:epass:location:' . $this->id,
            'type' => 'Location',
            'address' => $this->address->toArray(),
        ];
    }
}
