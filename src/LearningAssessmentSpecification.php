<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class LearningAssessmentSpecification extends Entity {

    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly Concept $assessmentType,
        public readonly GradingScheme $gradingScheme,
        public readonly Concept $language,
        public readonly Concept $mode,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => 'urn:epass:learningAssessmentSpec:' . $this->id,
            'type' => 'LearningAssessmentSpecification',
            'title' => $this->title->toArray(),
            'dcType' => [$this->assessmentType->toArray()],
            'gradingScheme' => $this->gradingScheme->toArray(),
            'language' => [$this->language->toArray()],
            'mode' => [$this->mode->toArray()],
        ];
    }
}
