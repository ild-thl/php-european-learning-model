<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\Organisation;
use IsyThl\EuropeanLearningModel\Core\LegalIdentifier;
use IsyThl\EuropeanLearningModel\Core\Location;
use IsyThl\EuropeanLearningModel\Core\ContactPoint;
use IsyThl\EuropeanLearningModel\Core\MediaObject;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;

final class Issuer extends Organisation {

    public function __construct(
        string $id,
        Location $location,
        LocalizedString $legalName,
        LegalIdentifier $registration,
        ?ContactPoint $contactPoint = null,
        ?MediaObject $logo = null,
    ) {
        parent::__construct($id, $location, $legalName, $registration, $contactPoint, $logo);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = parent::toArray();
        $data['id'] = $this->id;
        return $data;
    }
}
