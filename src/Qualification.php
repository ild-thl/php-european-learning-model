<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ConceptAssertions;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Core\CreditPoint;
use IsyThl\EuropeanLearningModel\Core\LearningOutcome;
use IsyThl\EuropeanLearningModel\Core\Note;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Qualification extends LearningAchievementSpecification {

    /**
     * @param list<CreditPoint> $creditPoints
     * @param list<string> $category
     * @param list<Concept> $qualificationCodes
    * @param list<LearningOutcome> $learningOutcomes
    * @param list<Concept> $educationSubjects
    * @param list<LearningActivity> $influencedBy
    * @param list<AwardingOpportunity> $awardingOpportunities
     */
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
        array $learningOutcomes = [],
        array $educationSubjects = [],
        public readonly ?Organisation $publisher = null,
        public readonly ?Note $learningOutcomeSummary = null,
        public readonly ?Note $entryRequirement = null,
        public readonly ?Qualification $specialisationOf = null,
        public readonly ?Qualification $generalisationOf = null,
        /** @var list<Qualification> */
        public readonly array $hasPart = [],
        /** @var list<Qualification> */
        public readonly array $isPartOf = [],
        /** @var list<LearningActivity> */
        public readonly array $influencedBy = [],
        /** @var list<AwardingOpportunity> */
        public readonly array $awardingOpportunities = [],
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
            learningOutcomes: $learningOutcomes,
            educationSubjects: $educationSubjects,
        );
        if (
            array_filter(
                $qualificationCodes,
                static fn ($qualificationCode): bool => !$qualificationCode instanceof Concept,
            ) !== []
        ) {
            throw new InvalidCredentialException('Qualification codes must be concepts.');
        }
        if (
            array_filter(
                $nqfLevels,
                static fn ($nqfLevel): bool => !$nqfLevel instanceof Concept,
            ) !== []
        ) {
            throw new InvalidCredentialException('NQF levels must be concepts.');
        }
        if ($eqfLevel !== null) {
            ConceptAssertions::assertScheme($eqfLevel, ElmVocabularySchemes::EQF, 'eqfLevel');
        }
        if (array_filter($hasPart, static fn ($part): bool => !$part instanceof self) !== []) {
            throw new InvalidCredentialException('Qualification parts must be Qualification objects.');
        }
        if (array_filter($isPartOf, static fn ($parent): bool => !$parent instanceof self) !== []) {
            throw new InvalidCredentialException('Qualification parents must be Qualification objects.');
        }
        if (array_filter($influencedBy, static fn ($activity): bool => !$activity instanceof LearningActivity) !== []) {
            throw new InvalidCredentialException('Qualification activities must be LearningActivity objects.');
        }
        if (
            array_filter(
                $awardingOpportunities,
                static fn ($opportunity): bool => !$opportunity instanceof AwardingOpportunity,
            ) !== []
        ) {
            throw new InvalidCredentialException('Awarding opportunities must be AwardingOpportunity objects.');
        }
        foreach ($nqfLevels as $nqfLevel) {
            ConceptAssertions::assertSchemePrefix($nqfLevel, ElmVocabularySchemes::QDR_BASE, 'nqfLevels');
        }
        if (
            array_filter(
                $accreditations,
                static fn ($accreditation): bool => !$accreditation instanceof Accreditation,
            ) !== []
        ) {
            throw new InvalidCredentialException('Accreditations must be Accreditation objects.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = parent::toArray();
        $data['id'] = 'urn:epass:qualification:' . $this->id;
        $data['type'] = 'Qualification';
        if ($this->isPartialQualification !== null) {
            $data['isPartialQualification'] = $this->isPartialQualification;
        }
        if ($this->qualificationCodes !== []) {
            $data['qualificationCode'] = array_map(
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
        if ($this->publisher !== null) {
            $data['publisher'] = $this->publisher->toArray();
        }
        if ($this->learningOutcomeSummary !== null) {
            $data['learningOutcomeSummary'] = $this->learningOutcomeSummary->toArray();
        }
        if ($this->entryRequirement !== null) {
            $data['entryRequirement'] = $this->entryRequirement->toArray();
        }
        if ($this->specialisationOf !== null) {
            $data['specialisationOf'] = $this->specialisationOf->toArray();
        }
        if ($this->generalisationOf !== null) {
            $data['generalisationOf'] = $this->generalisationOf->toArray();
        }
        if ($this->hasPart !== []) {
            $data['hasPart'] = array_map(
                static fn (self $part): array => $part->toArray(),
                $this->hasPart,
            );
        }
        if ($this->isPartOf !== []) {
            $data['isPartOf'] = array_map(
                static fn (self $parent): array => $parent->toArray(),
                $this->isPartOf,
            );
        }
        if ($this->influencedBy !== []) {
            $data['influencedBy'] = array_map(
                static fn (LearningActivity $activity): array => $activity->toArray(),
                $this->influencedBy,
            );
        }
        if ($this->awardingOpportunities !== []) {
            $data['awardingOpportunity'] = array_map(
                static fn (AwardingOpportunity $opportunity): array => $opportunity->toArray(),
                $this->awardingOpportunities,
            );
        }
        return $data;
    }
}
