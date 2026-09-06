<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

interface Clock {

    public function now(): int;
}
