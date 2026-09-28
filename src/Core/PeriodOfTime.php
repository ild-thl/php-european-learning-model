<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

class PeriodOfTime extends Entity {
    public function __construct(
        string $id,
        public readonly ?LocalizedString $prefLabel = null,
        public readonly ?DateTimeImmutable $startDate = null,
        public readonly ?DateTimeImmutable $endDate = null,
    ) {
        parent::__construct($id);
        if ($startDate === null && $endDate === null) {
            throw new InvalidCredentialException('A period of time requires a start or end date.');
        }
        if ($startDate !== null && $endDate !== null && $endDate < $startDate) {
            throw new InvalidCredentialException('A period of time cannot end before it starts.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'PeriodOfTime',
        ];
        if ($this->prefLabel !== null) {
            $data['prefLabel'] = $this->prefLabel->toArray();
        }
        if ($this->startDate !== null) {
            $data['startDate'] = DateTimeFormatter::format($this->startDate);
        }
        if ($this->endDate !== null) {
            $data['endDate'] = DateTimeFormatter::format($this->endDate);
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'PeriodOfTime') {
            throw new InvalidCredentialException('Data is not a PeriodOfTime.');
        }
        if (!isset($data['id'])) {
            throw new InvalidCredentialException('PeriodOfTime is missing id.');
        }

        return new self(
            $data['id'],
            isset($data['prefLabel']) ? LocalizedString::fromArray($data['prefLabel']) : null,
            isset($data['startDate']) ? new DateTimeImmutable($data['startDate']) : null,
            isset($data['prefLabel']) ? new DateTimeImmutable($data['prefLabel']) : null,
        );
    }
}
