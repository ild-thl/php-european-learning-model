<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class LearningAchievement extends Claim {

    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly AwardingProcess $awardedBy,
        public readonly LearningAchievementSpecification $specifiedBy,
        public readonly ?CreditPoint $creditReceived = null,
        /** @var list<LearningAssessment> */
        public readonly array $provenBy = [],
    ) {
        parent::__construct($id);
    }

    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:learningAchievement:' . $this->id,
            'type' => 'LearningAchievement',
            'awardedBy' => $this->awardedBy->toArray(),
            'title' => $this->title->toArray(),
            'specifiedBy' => $this->specifiedBy->toArray(),
        ];
        if ($this->creditReceived !== null) {
            $data['creditReceived'] = $this->creditReceived->toArray();
        }
        if ($this->provenBy !== []) {
            $data['provenBy'] = array_map(
                static fn (LearningAssessment $assessment): array => $assessment->toArray(),
                $this->provenBy,
            );
        }
        return $data;
    }
}
