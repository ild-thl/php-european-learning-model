<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use DateTimeZone;

final class DateTimeFormatter {

    public static function format(DateTimeImmutable $date): string {
        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\\TH:i:s\\Z');
    }
}
