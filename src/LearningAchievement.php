<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class LearningAchievement extends Claim {

    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly AwardingProcess $awardedBy,
        public readonly LearningAchievementSpecification $specifiedBy,
        public readonly ?CreditPoint $creditReceived = null,
        /** @var list<LearningAssessment> */
        public readonly array $provenBy = [],
        /** @var list<Identifier|LegalIdentifier> */
        public readonly array $identifiers = [],
        /** @var list<LearningActivity> */
        public readonly array $influencedBy = [],
        /** @var list<LearningEntitlement> */
        public readonly array $entitlesTo = [],
    ) {
        parent::__construct($id);
        $invalidAssessments = array_filter(
            $provenBy,
            static fn ($assessment): bool => !$assessment instanceof LearningAssessment,
        );
        if ($invalidAssessments !== []) {
            throw new InvalidCredentialException('Achievement assessments must be LearningAssessment objects.');
        }
        $invalidIdentifiers = array_filter(
            $identifiers,
            static fn ($identifier): bool => (
                !$identifier instanceof Identifier
                && !$identifier instanceof LegalIdentifier
            ),
        );
        if ($invalidIdentifiers !== []) {
            throw new InvalidCredentialException('Achievement identifiers must be Identifier objects.');
        }
        $invalidActivities = array_filter(
            $influencedBy,
            static fn ($activity): bool => !$activity instanceof LearningActivity,
        );
        if ($invalidActivities !== []) {
            throw new InvalidCredentialException('Achievement activities must be LearningActivity objects.');
        }
        $invalidEntitlements = array_filter(
            $entitlesTo,
            static fn ($entitlement): bool => !$entitlement instanceof LearningEntitlement,
        );
        if ($invalidEntitlements !== []) {
            throw new InvalidCredentialException('Achievement entitlements must be LearningEntitlement objects.');
        }
    }

    /** @return array<string, mixed> */
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
        if ($this->identifiers !== []) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifiers,
            );
        }
        if ($this->influencedBy !== []) {
            $data['influencedBy'] = array_map(
                static fn (LearningActivity $activity): array => $activity->toArray(),
                $this->influencedBy,
            );
        }
        if ($this->entitlesTo !== []) {
            $data['entitlesTo'] = array_map(
                static fn (LearningEntitlement $entitlement): array => $entitlement->toArray(),
                $this->entitlesTo,
            );
        }
        return $data;
    }
}
