<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

class Organisation extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(
        string $id,
        public readonly Location $location,
        public readonly LocalizedString $legalName,
        public readonly ?LegalIdentifier $registration = null,
        public readonly ?ContactPoint $contactPoint = null,
        public readonly ?MediaObject $logo = null,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:org:' . $this->id,
            'type' => 'Organisation',
            'location' => $this->location->toArray(),
            'legalName' => $this->legalName->toArray(),
        ];
        if ($this->contactPoint !== null) {
            $data['contactPoint'] = [$this->contactPoint->toArray()];
        }
        if ($this->logo !== null) {
            $data['logo'] = $this->logo->toArray();
        }
        if ($this->registration !== null) {
            $data['registration'] = $this->registration->toArray();
        }
        return $data;
    }
}
