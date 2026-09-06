<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

enum EdcProfile: string {
    case GENERIC_NO_CV = 'http://data.europa.eu/snb/model/ap/edc-generic-no-cv';
    case GENERIC_FULL = 'http://data.europa.eu/snb/model/ap/edc-generic-full';

    public function resourceName(): string {
        return match ($this) {
            self::GENERIC_NO_CV => 'EDC-generic-no-cv.rdf',
            self::GENERIC_FULL => 'EDC-generic-full.rdf',
        };
    }
}
