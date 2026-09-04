<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

class LearningAchievementSpecification extends Entity
{
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
    ) {
        parent::__construct($id);
        if (array_filter(
            $creditPoints,
            static fn ($creditPoint): bool => !$creditPoint instanceof CreditPoint,
        ) !== []) {
            throw new \InvalidArgumentException('Specifications accept only credit points.');
        }
        if (array_filter($category, static fn ($value): bool => !is_string($value) || $value === '') !== []) {
            throw new \InvalidArgumentException('Specification categories must be non-empty strings.');
        }
        if (array_filter(
            $learningOutcomes,
            static fn ($learningOutcome): bool => !$learningOutcome instanceof LearningOutcome,
        ) !== []) {
            throw new \InvalidArgumentException('Learning outcomes must be LearningOutcome objects.');
        }
        if (array_filter(
            $additionalNotes,
            static fn ($note): bool => !$note instanceof Note,
        ) !== []) {
            throw new \InvalidArgumentException('Specification notes must be Note objects.');
        }
        foreach ([$maximumDuration, $volumeOfLearning] as $duration) {
            if ($duration !== null && preg_match('/^P(?:\\d+Y)?(?:\\d+M)?(?:\\d+D)?(?:T(?=\\d)(?:\\d+H)?(?:\\d+M)?(?:\\d+(?:\\.\\d+)?S)?)?$/', $duration) !== 1) {
                throw new \InvalidArgumentException('Specification durations must use ISO 8601 format.');
            }
        }
    }

    public function toArray(): array
    {
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
        return $data;
    }
}