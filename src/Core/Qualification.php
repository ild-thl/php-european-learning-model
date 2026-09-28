<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Qualification extends LearningAchievementSpecification {
    /**
     * @param  list<Identifier|LegalIdentifier>|null  $identifier
     * @param  list<LocalizedString>|null  $altLabel
     * @param  list<Concept>|null  $dcType
     * @param  list<Concept>|null  $language
     * @param  list<Note>|null  $additionalNote
     * @param  list<WebResource>|null  $supplementaryDocument
     * @param  list<Qualification>|null  $generalisationOf
     * @param  list<Qualification>|null  $specialisationOf
     * @param  list<CreditPoint>|null  $creditPoint
     * @param  list<Qualification>|null  $hasPart
     * @param  list<Qualification>|null  $isPartOf
     * @param  list<Concept>|null  $mode
     * @param  list<WebResource>|null  $homepage
     * @param  list<string>|null  $category
     * @param  list<Concept>|null  $targetGroup
     * @param  list<AwardingOpportunity>|null  $awardingOpportunity
     * @param  list<Concept>|null  $educationSubject
     * @param  list<LearningOutcome>|null  $learningOutcome
     * @param  list<LearningActivitySpecification>|null  $influencedBy
     * @param  list<LearningAssessmentSpecification>|null  $provenBy
     * @param  list<Concept>|null  $iscedfCode
     * @param  list<LearningEntitlementSpecification>|null  $entitlesTo
     * @param  list<Concept>|null  $educationLevel
     * @param  list<Concept>|null  $qualificationCode
     * @param  list<Concept>|null  $nqfLevel
     * @param  list<Accreditation>|null  $accreditation
     */
    public function __construct(
        string $id,
        LocalizedString $title,
        ?array $identifier = null,
        ?array $altLabel = null,
        ?Note $learningOutcomeSummary = null,
        ?Duration $volumeOfLearning = null,
        ?Note $entryRequirement = null,
        ?Concept $learningSetting = null,
        ?string $status = null,
        ?Duration $maximumDuration = null,
        ?array $dcType = null,
        ?array $language = null,
        ?LocalizedString $description = null,
        ?array $additionalNote = null,
        ?array $supplementaryDocument = null,
        ?array $generalisationOf = null,
        ?array $specialisationOf = null,
        ?array $creditPoint = null,
        ?array $hasPart = null,
        ?array $isPartOf = null,
        ?array $mode = null,
        ?array $homepage = null,
        ?array $category = null,
        ?array $targetGroup = null,
        ?array $awardingOpportunity = null,
        ?array $educationSubject = null,
        ?array $learningOutcome = null,
        ?array $influencedBy = null,
        ?array $provenBy = null,
        ?array $iscedfCode = null,
        ?array $entitlesTo = null,
        ?array $educationLevel = null,
        ?int $order = null,
        ?DateTimeImmutable $modified = null,
        public readonly ?bool $isPartialQualification = null,
        public readonly ?array $qualificationCode = null,
        public readonly ?array $nqfLevel = null,
        public readonly ?Concept $eqfLevel = null,
        public readonly ?array $accreditation = null,
    ) {
        foreach (
            [
                'generalisationOf' => $generalisationOf,
                'specialisationOf' => $specialisationOf,
                'hasPart' => $hasPart,
                'isPartOf' => $isPartOf,
            ] as $field => $values
        ) {
            if ($values !== null && array_filter($values, static fn ($value): bool => !$value instanceof self) !== []) {
                throw new InvalidCredentialException(sprintf('%s must contain only Qualification objects.', $field));
            }
        }
        parent::__construct(
            $id,
            $title,
            $identifier,
            $altLabel,
            $learningOutcomeSummary,
            $volumeOfLearning,
            $entryRequirement,
            $learningSetting,
            $status,
            $maximumDuration,
            $dcType,
            $language,
            $description,
            $additionalNote,
            $supplementaryDocument,
            $generalisationOf,
            $specialisationOf,
            $creditPoint,
            $hasPart,
            $isPartOf,
            $mode,
            $homepage,
            $category,
            $targetGroup,
            $awardingOpportunity,
            $educationSubject,
            $learningOutcome,
            $influencedBy,
            $provenBy,
            $iscedfCode,
            $entitlesTo,
            $educationLevel,
            $order,
            $modified,
        );
        if ($eqfLevel !== null) {
            ConceptAssertions::assertScheme($eqfLevel, ElmVocabularySchemes::EQF, 'EQFLevel');
        }
        if ($nqfLevel !== null) {
            foreach ($nqfLevel as $level) {
                ConceptAssertions::assertSchemePrefix($level, ElmVocabularySchemes::QDR_BASE, 'NQFLevel');
            }
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = parent::toArray();
        $data['type'] = 'Qualification';
        if ($this->isPartialQualification !== null) {
            $data['isPartialQualification'] = $this->isPartialQualification;
        }
        if ($this->qualificationCode !== null) {
            $data['qualificationCode'] = array_map(
                static fn (Concept $qualificationCode): array => $qualificationCode->toArray(),
                $this->qualificationCode,
            );
        }
        if ($this->nqfLevel !== null) {
            $data['NQFLevel'] = array_map(
                static fn (Concept $nqfLevel): array => $nqfLevel->toArray(),
                $this->nqfLevel,
            );
        }
        if ($this->eqfLevel !== null) {
            $data['EQFLevel'] = $this->eqfLevel->toArray();
        }
        if ($this->accreditation !== null) {
            $data['accreditation'] = array_map(
                static fn (Accreditation $accreditation): array => $accreditation->toArray(),
                $this->accreditation,
            );
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (($data['type'] ?? null) !== 'Qualification') {
            throw new InvalidCredentialException('Data is not a Qualification.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('Data is missing title');
        }

        $shared = LearningAchievementSpecification::fromArray($data);

        return new self(
            $shared->id,
            $shared->title,
            $shared->identifier,
            $shared->altLabel,
            $shared->learningOutcomeSummary,
            $shared->volumeOfLearning,
            $shared->entryRequirement,
            $shared->learningSetting,
            $shared->status,
            $shared->maximumDuration,
            $shared->dcType,
            $shared->language,
            $shared->description,
            $shared->additionalNote,
            $shared->supplementaryDocument,
            self::qualifications($shared->generalisationOf, 'generalisationOf'),
            self::qualifications($shared->specialisationOf, 'specialisationOf'),
            $shared->creditPoint,
            self::qualifications($shared->hasPart, 'hasPart'),
            self::qualifications($shared->isPartOf, 'isPartOf'),
            $shared->mode,
            $shared->homepage,
            $shared->category,
            $shared->targetGroup,
            $shared->awardingOpportunity,
            $shared->educationSubject,
            $shared->learningOutcome,
            $shared->influencedBy,
            $shared->provenBy,
            $shared->iscedfCode,
            $shared->entitlesTo,
            $shared->educationLevel,
            $shared->order,
            $shared->modified,
            $data['isPartialQualification'] ?? null,
            isset($data['qualificationCode']) ? array_map(static fn (array $concept): Concept => Concept::fromArray($concept), $data['qualificationCode']) : null,
            isset($data['NQFLevel']) ? array_map(static fn (array $concept): Concept => Concept::fromArray($concept), $data['NQFLevel']) : null,
            isset($data['EQFLevel']) ? Concept::fromArray($data['EQFLevel']) : null,
            isset($data['accreditation']) ? array_map(static fn (array $accreditation): Accreditation => Accreditation::fromArray($accreditation), $data['accreditation']) : null,
        );
    }

    /**
     * @param  list<LearningAchievementSpecification|Qualification>|null  $values
     * @return list<Qualification>|null
     */
    private static function qualifications(?array $values, string $field): ?array {
        if ($values === null) {
            return null;
        }
        foreach ($values as $value) {
            if (!$value instanceof self) {
                throw new InvalidCredentialException(sprintf('%s must contain only Qualification objects.', $field));
            }
        }

        return $values;
    }
}
