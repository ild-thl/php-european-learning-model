<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\Address;
use IsyThl\EuropeanLearningModel\Core\EmailAddress;

final class ContactPoint extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(
        string $id,
        public readonly ?Address $address = null,
        public readonly ?EmailAddress $emailAddress = null,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = ['id' => 'urn:epass:contactPoint:' . $this->id, 'type' => 'ContactPoint'];
        if ($this->address !== null) {
            $data['address'] = [$this->address->toArray()];
        }
        if ($this->emailAddress !== null) {
            $data['emailAddress'] = [$this->emailAddress->toArray()];
        }
        return $data;
    }
}
