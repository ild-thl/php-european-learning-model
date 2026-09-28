<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Core\AwardingProcess;
use IsyThl\EuropeanLearningModel\Core\DateTimeFormatter;
use IsyThl\EuropeanLearningModel\Core\LearningAchievementSpecification;
use IsyThl\EuropeanLearningModel\Core\LearningEntitlementSpecification;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningEntitlement extends Claim {
    /**
     * @param  list<LearningAchievementSpecification>  $entitledBy
     */
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly AwardingProcess $awardedBy,
        public readonly ?LocalizedString $description = null,
        public readonly array $entitledBy = [],
        public readonly ?DateTimeImmutable $issued = null,
        public readonly ?DateTimeImmutable $expiryDate = null,
        public readonly ?LearningEntitlementSpecification $specifiedBy = null,
    ) {
        parent::__construct($id);
        if (
            array_filter(
                $entitledBy,
                static fn ($item): bool => (
                    !$item instanceof LearningAchievementSpecification
                ),
            ) !== []
        ) {
            throw new InvalidCredentialException(
                'Entitlement provenance must be achievement specifications or qualifications.',
            );
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningEntitlement',
            'title' => $this->title->toArray(),
            'awardedBy' => $this->awardedBy->toArray(),
        ];
        if ($this->issued !== null) {
            $data['issued'] = DateTimeFormatter::format($this->issued);
        }
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->entitledBy !== []) {
            $data['entitledBy'] = array_map(
                static fn (LearningAchievementSpecification $item): array => $item->toArray(),
                $this->entitledBy,
            );
        }
        if ($this->expiryDate !== null) {
            $data['expiryDate'] = DateTimeFormatter::format($this->expiryDate);
        }
        if ($this->specifiedBy !== null) {
            $data['specifiedBy'] = $this->specifiedBy->toArray();
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'LearningEntitlement') {
            throw new InvalidCredentialException('Data is not a LearningEntitlement.');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            AwardingProcess::fromArray($data['awardedBy']),
            isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            isset($data['entitledBy']) ? array_map(
                static fn (array $item): LearningAchievementSpecification => LearningAchievementSpecification::fromArray($item),
                $data['entitledBy'],
            ) : [],
            isset($data['issued']) ? new DateTimeImmutable($data['issued']) : null,
            isset($data['expiryDate']) ? new DateTimeImmutable($data['expiryDate']) : null,
            isset($data['specifiedBy']) ? LearningEntitlementSpecification::fromArray($data['specifiedBy']) : null,
        );
    }
}
