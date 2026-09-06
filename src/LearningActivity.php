<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\AwardingProcess;
use IsyThl\EuropeanLearningModel\Core\LearningActivitySpecification;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningActivity extends Claim {

    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly AwardingProcess $awardedBy,
        public readonly LearningActivitySpecification $specifiedBy,
        public readonly ?LocalizedString $description = null,
        /** @var list<LearningActivity> */
        public readonly array $hasPart = [],
        /** @var list<LearningActivity> */
        public readonly array $isPartOf = [],
    ) {
        parent::__construct($id);
        if (array_filter($hasPart, static fn ($activity): bool => !$activity instanceof self) !== []) {
            throw new InvalidCredentialException('Activity parts must be LearningActivity objects.');
        }
        if (array_filter($isPartOf, static fn ($activity): bool => !$activity instanceof self) !== []) {
            throw new InvalidCredentialException('Activity parents must be LearningActivity objects.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:activity:' . $this->id,
            'type' => 'LearningActivity',
            'awardedBy' => $this->awardedBy->toArray(),
            'title' => $this->title->toArray(),
            'specifiedBy' => $this->specifiedBy->toArray(),
        ];
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->hasPart !== []) {
            $data['hasPart'] = array_map(
                static fn (self $activity): array => $activity->toArray(),
                $this->hasPart,
            );
        }
        if ($this->isPartOf !== []) {
            $data['isPartOf'] = array_map(
                static fn (self $activity): array => $activity->toArray(),
                $this->isPartOf,
            );
        }

        return $data;
    }
}
