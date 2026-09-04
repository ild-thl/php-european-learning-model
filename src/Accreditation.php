<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class Accreditation extends Entity {

    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly Organisation $accreditingAgent,
    ) {
        parent::__construct($id);
    }

    public function toArray(): array {
        return [
            'id' => 'urn:epass:accreditation:' . $this->id,
            'type' => 'Accreditation',
            'title' => $this->title->toArray(),
            'accreditingAgent' => $this->accreditingAgent->toArray(),
        ];
    }
}
