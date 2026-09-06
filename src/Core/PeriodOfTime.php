<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use DateTimeZone;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class PeriodOfTime {

    public function __construct(
        public readonly ?DateTimeImmutable $startDate = null,
        public readonly ?DateTimeImmutable $endDate = null,
    ) {
        if ($startDate === null && $endDate === null) {
            throw new InvalidCredentialException('A period of time requires a start or end date.');
        }
        if ($startDate !== null && $endDate !== null && $endDate < $startDate) {
            throw new InvalidCredentialException('A period of time cannot end before it starts.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = ['type' => 'PeriodOfTime'];
        if ($this->startDate !== null) {
            $data['startDate'] = self::formatDate($this->startDate);
        }
        if ($this->endDate !== null) {
            $data['endDate'] = self::formatDate($this->endDate);
        }
        return $data;
    }

    private static function formatDate(DateTimeImmutable $date): string {
        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\\TH:i:s\\Z');
    }
}
