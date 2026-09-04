<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class Qualification extends LearningAchievementSpecification
{
    /** @param list<Concept> $qualificationCodes */
    public function __construct(
        string $id,
        LocalizedString $title,
        ?LocalizedString $description = null,
        array $creditPoints = [],
        ?Concept $language = null,
        array $category = [],
        ?string $maximumDuration = null,
        ?string $volumeOfLearning = null,
        public readonly ?bool $isPartialQualification = null,
        public readonly array $qualificationCodes = [],
    ) {
        parent::__construct(
            $id,
            $title,
            $description,
            $creditPoints,
            $language,
            $category,
            $maximumDuration,
            $volumeOfLearning,
        );
        if (array_filter(
            $qualificationCodes,
            static fn ($qualificationCode): bool => !$qualificationCode instanceof Concept,
        ) !== []) {
            throw new \InvalidArgumentException('Qualification codes must be concepts.');
        }
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['id'] = 'urn:epass:qualification:' . $this->id;
        $data['type'] = 'Qualification';
        if ($this->isPartialQualification !== null) {
            $data['isPartialQualification'] = $this->isPartialQualification;
        }
        if ($this->qualificationCodes !== []) {
            $data['qualificationCodes'] = array_map(
                static fn (Concept $qualificationCode): array => $qualificationCode->toArray(),
                $this->qualificationCodes,
            );
        }
        return $data;
    }
}