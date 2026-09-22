<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanLearningModel\Core\ContactPoint;
use IsyThl\EuropeanLearningModel\Core\LegalIdentifier;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Core\Location;
use IsyThl\EuropeanLearningModel\Core\MediaObject;
use IsyThl\EuropeanLearningModel\Core\Organisation;

final class Issuer extends Organisation {

    /** @param list<Location> $location */
    public function __construct(
        string $id,
        LocalizedString $legalName,
        array $location,
        LegalIdentifier $registration,
        ?ContactPoint $contactPoint = null,
        ?MediaObject $logo = null,
    ) {
        parent::__construct(
            $id,
            $legalName,
            $location,
            registration: $registration,
            contactPoint: $contactPoint,
            logo: $logo,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = parent::toArray();
        $data['id'] = $this->id;
        return $data;
    }
}
