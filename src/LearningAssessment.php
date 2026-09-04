<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class LearningAssessment extends Claim {

    public function __construct(
        string $id,
        public readonly AwardingProcess $awardedBy,
        public readonly LocalizedString $title,
        public readonly Note $grade,
        public readonly Concept $idVerification,
        public readonly LearningAssessmentSpecification $specifiedBy,
        /** @var list<LearningAssessment> */
        public readonly array $hasPart = [],
        /** @var list<LearningAssessment> */
        public readonly array $isPartOf = [],
    ) {
        parent::__construct($id);
        if (array_filter($hasPart, static fn ($assessment): bool => !$assessment instanceof self) !== []) {
            throw new InvalidCredentialException('Assessment parts must be LearningAssessment objects.');
        }
        if (array_filter($isPartOf, static fn ($assessment): bool => !$assessment instanceof self) !== []) {
            throw new InvalidCredentialException('Assessment parents must be LearningAssessment objects.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:learningAssessment:' . $this->id,
            'type' => 'LearningAssessment',
            'awardedBy' => $this->awardedBy->toArray(),
            'title' => $this->title->toArray(),
            'grade' => $this->grade->toArray(),
            'idVerification' => $this->idVerification->toArray(),
            'specifiedBy' => $this->specifiedBy->toArray(),
        ];
        if ($this->hasPart !== []) {
            $data['hasPart'] = array_map(
                static fn (self $assessment): array => $assessment->toArray(),
                $this->hasPart,
            );
        }
        if ($this->isPartOf !== []) {
            $data['isPartOf'] = array_map(
                static fn (self $assessment): array => $assessment->toArray(),
                $this->isPartOf,
            );
        }

        return $data;
    }
}
