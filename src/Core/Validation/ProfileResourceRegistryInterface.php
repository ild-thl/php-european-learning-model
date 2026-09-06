<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core\Validation;

interface ProfileResourceRegistryInterface {

    public function get(string $profileResource): string;
}
