<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class LearningAssessment extends Claim {

    public function __construct(
        string $id,
        public readonly AwardingProcess $awardedBy,
        public readonly LocalizedString $title,
        public readonly Note $grade,
        public readonly Concept $idVerification,
        public readonly LearningAssessmentSpecification $specifiedBy,
    ) {
        parent::__construct($id);
    }

    public function toArray(): array {
        return [
            'id' => 'urn:epass:learningAssessment:' . $this->id,
            'type' => 'LearningAssessment',
            'awardedBy' => $this->awardedBy->toArray(),
            'title' => $this->title->toArray(),
            'grade' => $this->grade->toArray(),
            'idVerification' => $this->idVerification->toArray(),
            'specifiedBy' => $this->specifiedBy->toArray(),
        ];
    }
}
