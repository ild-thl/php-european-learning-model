<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

interface Clock {

    public function now(): int;
}
