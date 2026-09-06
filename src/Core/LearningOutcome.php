<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\ConceptAssertions;
use IsyThl\EuropeanLearningModel\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Core\Note;

final class LearningOutcome extends \IsyThl\EuropeanLearningModel\Core\Entity {

    /** @param list<Concept> $relatedSkills */
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly array $relatedSkills = [],
        /** @var list<Note> */
        public readonly array $additionalNotes = [],
        /** @var list<Concept> */
        public readonly array $relatedEscosSkills = [],
        public readonly ?Concept $reusabilityLevel = null,
    ) {
        parent::__construct($id);
        if (
            array_filter(
                $relatedSkills,
                static fn ($relatedSkill): bool => !$relatedSkill instanceof Concept,
            ) !== []
        ) {
            throw new InvalidCredentialException('Learning outcome skills must be concepts.');
        }
        ConceptAssertions::assertSchemes(
            $relatedSkills,
            ElmVocabularySchemes::DCF_SKILLS,
            'relatedSkills',
        );
        if (
            array_filter(
                $additionalNotes,
                static fn ($note): bool => !$note instanceof Note,
            ) !== []
        ) {
            throw new InvalidCredentialException('Learning outcome notes must be Note objects.');
        }
        ConceptAssertions::assertSchemes(
            $relatedEscosSkills,
            ElmVocabularySchemes::ESCO_SKILLS,
            'relatedEscosSkills',
        );
        if ($reusabilityLevel !== null) {
            ConceptAssertions::assertScheme(
                $reusabilityLevel,
                ElmVocabularySchemes::SKILL_REUSE_LEVEL,
                'reusabilityLevel',
            );
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:LearningOutcome:' . $this->id,
            'type' => 'LearningOutcome',
            'title' => $this->title->toArray(),
        ];
        if ($this->relatedSkills !== []) {
            $data['relatedSkills'] = array_map(
                static fn (Concept $relatedSkill): array => $relatedSkill->toArray(),
                $this->relatedSkills,
            );
        }
        if ($this->relatedEscosSkills !== []) {
            $data['relatedESCOSkill'] = array_map(
                static fn (Concept $skill): array => $skill->toArray(),
                $this->relatedEscosSkills,
            );
        }
        if ($this->reusabilityLevel !== null) {
            $data['reusabilityLevel'] = $this->reusabilityLevel->toArray();
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
