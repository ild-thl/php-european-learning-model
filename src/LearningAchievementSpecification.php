<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

class LearningAchievementSpecification extends Entity {

    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly ?LocalizedString $description = null,
        /** @var list<CreditPoint> */
        public readonly array $creditPoints = [],
        public readonly ?Concept $language = null,
        /** @var list<string> */
        public readonly array $category = [],
        public readonly ?string $maximumDuration = null,
        public readonly ?string $volumeOfLearning = null,
        /** @var list<LearningOutcome> */
        public readonly array $learningOutcomes = [],
        /** @var list<Note> */
        public readonly array $additionalNotes = [],
        /** @var list<WebResource> */
        public readonly array $supplementaryDocuments = [],
        /** @var list<Concept> */
        public readonly array $educationLevels = [],
        /** @var list<Concept> */
        public readonly array $educationSubjects = [],
    ) {
        parent::__construct($id);
        if ($language !== null) {
            ConceptAssertions::assertScheme($language, ElmVocabularySchemes::LANGUAGE, 'language');
        }
        ConceptAssertions::assertSchemes($educationSubjects, ElmVocabularySchemes::ISCED_F, 'educationSubjects');
        if (
            array_filter(
                $creditPoints,
                static fn ($creditPoint): bool => !$creditPoint instanceof CreditPoint,
            ) !== []
        ) {
            throw new InvalidCredentialException('Specifications accept only credit points.');
        }
        if (array_filter($category, static fn ($value): bool => !is_string($value) || $value === '') !== []) {
            throw new InvalidCredentialException('Specification categories must be non-empty strings.');
        }
        if (
            array_filter(
                $learningOutcomes,
                static fn ($learningOutcome): bool => !$learningOutcome instanceof LearningOutcome,
            ) !== []
        ) {
            throw new InvalidCredentialException('Learning outcomes must be LearningOutcome objects.');
        }
        if (
            array_filter(
                $additionalNotes,
                static fn ($note): bool => !$note instanceof Note,
            ) !== []
        ) {
            throw new InvalidCredentialException('Specification notes must be Note objects.');
        }
        if (
            array_filter(
                $supplementaryDocuments,
                static fn ($document): bool => !$document instanceof WebResource,
            ) !== []
        ) {
            throw new InvalidCredentialException('Supplementary documents must be WebResource objects.');
        }
        $educationConcepts = [
            'education level' => $educationLevels,
            'education subject' => $educationSubjects,
        ];
        foreach ($educationConcepts as $name => $concepts) {
            if (array_filter($concepts, static fn ($concept): bool => !$concept instanceof Concept) !== []) {
                throw new InvalidCredentialException(sprintf('Specification %s values must be concepts.', $name));
            }
        }
        foreach ([$maximumDuration, $volumeOfLearning] as $duration) {
            $isValidDuration = $duration === null || preg_match(
                '/^P(?:\\d+Y)?(?:\\d+M)?(?:\\d+D)?(?:T(?=\\d)(?:\\d+H)?(?:\\d+M)?(?:\\d+(?:\\.\\d+)?S)?)?$/',
                $duration,
            ) === 1;
            if (!$isValidDuration) {
                throw new InvalidCredentialException('Specification durations must use ISO 8601 format.');
            }
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:learningAchievementSpecification:' . $this->id,
            'type' => 'LearningAchievementSpecification',
            'title' => $this->title->toArray(),
        ];
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->creditPoints !== []) {
            $data['creditPoint'] = array_map(
                static fn (CreditPoint $creditPoint): array => $creditPoint->toArray(),
                $this->creditPoints,
            );
        }
        if ($this->language !== null) {
            $data['language'] = [$this->language->toArray()];
        }
        if ($this->category !== []) {
            $data['category'] = $this->category;
        }
        if ($this->maximumDuration !== null) {
            $data['maximumDuration'] = $this->maximumDuration;
        }
        if ($this->volumeOfLearning !== null) {
            $data['volumeOfLearning'] = $this->volumeOfLearning;
        }
        if ($this->learningOutcomes !== []) {
            $data['learningOutcome'] = array_map(
                static fn (LearningOutcome $learningOutcome): array => $learningOutcome->toArray(),
                $this->learningOutcomes,
            );
        }
        if ($this->additionalNotes !== []) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNotes,
            );
        }
        if ($this->supplementaryDocuments !== []) {
            $data['supplementaryDocument'] = array_map(
                static fn (WebResource $document): array => $document->toArray(),
                $this->supplementaryDocuments,
            );
        }
        if ($this->educationLevels !== []) {
            $data['educationLevel'] = array_map(
                static fn (Concept $level): array => $level->toArray(),
                $this->educationLevels,
            );
        }
        if ($this->educationSubjects !== []) {
            $data['educationSubject'] = array_map(
                static fn (Concept $subject): array => $subject->toArray(),
                $this->educationSubjects,
            );
        }
        return $data;
    }
}
