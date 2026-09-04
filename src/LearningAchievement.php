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
        if ($this->identifiers !== []) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifiers,
            );
        }
        return $data;
    }
}
