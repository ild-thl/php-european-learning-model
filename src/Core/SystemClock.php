<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

final class SystemClock implements Clock {

    public function now(): int {
        return time();
    }
}
