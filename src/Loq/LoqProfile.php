<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

enum LoqProfile: string {
    case LOQ_CONSTRAINTS = 'http://data.europa.eu/snb/model/application-profile/loq-constraints';

    public function resourceName(): string {
        return 'LOQ-constraints.rdf';
    }
}
