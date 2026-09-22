<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Core\DateTimeFormatter;
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
            $data['startDate'] = DateTimeFormatter::format($this->startDate);
        }
        if ($this->endDate !== null) {
            $data['endDate'] = DateTimeFormatter::format($this->endDate);
        }
        return $data;
    }
}
