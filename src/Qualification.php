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
        public readonly ?Concept $eqfLevel = null,
        /** @var list<Concept> */
        public readonly array $nqfLevels = [],
        /** @var list<Accreditation> */
        public readonly array $accreditations = [],
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
        if (array_filter(
            $nqfLevels,
            static fn ($nqfLevel): bool => !$nqfLevel instanceof Concept,
        ) !== []) {
            throw new \InvalidArgumentException('NQF levels must be concepts.');
        }
        if (array_filter(
            $accreditations,
            static fn ($accreditation): bool => !$accreditation instanceof Accreditation,
        ) !== []) {
            throw new \InvalidArgumentException('Accreditations must be Accreditation objects.');
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
        if ($this->nqfLevels !== []) {
            $data['nqfLevel'] = array_map(
                static fn (Concept $nqfLevel): array => $nqfLevel->toArray(),
                $this->nqfLevels,
            );
        }
        if ($this->eqfLevel !== null) {
            $data['eqfLevel'] = $this->eqfLevel->toArray();
        }
        if ($this->accreditations !== []) {
            $data['accreditation'] = array_map(
                static fn (Accreditation $accreditation): array => $accreditation->toArray(),
                $this->accreditations,
            );
        }
        return $data;
    }
}