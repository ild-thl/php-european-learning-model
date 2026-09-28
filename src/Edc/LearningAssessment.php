<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanLearningModel\Core\AwardingProcess;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ConceptAssertions;
use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\LearningAssessmentSpecification;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Core\Note;

final class LearningAssessment extends Claim {
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly Note $grade,
        public readonly AwardingProcess $awardedBy,
        public readonly ?Concept $idVerification = null,
        public readonly ?LearningAssessmentSpecification $specifiedBy = null,
        /** @var list<LearningAssessment>|null */
        public readonly ?array $hasPart = null,
        /** @var list<LearningAssessment>|null */
        public readonly ?array $isPartOf = null,
    ) {
        parent::__construct($id);
        if (isset($idVerification)) {
            ConceptAssertions::assertScheme(
                $idVerification,
                ElmVocabularySchemes::SUPERVISION_VERIFICATION,
                'idVerification',
            );
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningAssessment',
            'title' => $this->title->toArray(),
            'grade' => $this->grade->toArray(),
            'awardedBy' => $this->awardedBy->toArray(),
        ];
        if ($this->idVerification !== null) {
            $data['idVerification'] = $this->idVerification->toArray();
        }
        if ($this->specifiedBy !== null) {
            $data['specifiedBy'] = $this->specifiedBy->toArray();
        }
        if ($this->hasPart !== null) {
            $data['hasPart'] = array_map(
                static fn (self $assessment): array => $assessment->toArray(),
                $this->hasPart,
            );
        }
        if ($this->isPartOf !== null) {
            $data['isPartOf'] = array_map(
                static fn (self $assessment): array => $assessment->toArray(),
                $this->isPartOf,
            );
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            Note::fromArray($data['grade']),
            AwardingProcess::fromArray($data['awardedBy']),
            isset($data['idVerification']) ? Concept::fromArray($data['idVerification']) : null,
            isset($data['specifiedBy']) ? LearningAssessmentSpecification::fromArray($data['specifiedBy']) : null,
            isset($data['hasPart']) ? array_map(
                static fn (array $assessment): self => self::fromArray($assessment),
                $data['hasPart'],
            ) : null,
            isset($data['isPartOf']) ? array_map(
                static fn (array $assessment): self => self::fromArray($assessment),
                $data['isPartOf'],
            ) : null,
        );
    }
}
