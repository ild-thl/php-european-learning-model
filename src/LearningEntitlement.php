<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\AwardingProcess;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningEntitlement extends Claim {

    /**
    * @param list<LearningAchievementSpecification> $entitledBy
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
            'id' => 'urn:epass:learningEntitlement:' . $this->id,
            'type' => 'LearningEntitlement',
            'awardedBy' => $this->awardedBy->toArray(),
            'title' => $this->title->toArray(),
        ];
        if ($this->issued !== null) {
            $data['issued'] = $this->formatDate($this->issued);
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
            $data['expiryDate'] = $this->formatDate($this->expiryDate);
        }
        if ($this->specifiedBy !== null) {
            $data['specifiedBy'] = $this->specifiedBy->toArray();
        }

        return $data;
    }

    private function formatDate(DateTimeImmutable $date): string {
        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }
}
