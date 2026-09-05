<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

interface Clock {

    public function now(): int;
}
