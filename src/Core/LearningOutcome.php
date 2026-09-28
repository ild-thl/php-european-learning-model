<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningOutcome extends Entity {
    /** @param list<Concept> $relatedSkill*/
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        /** @var list<Note>|null */
        public readonly ?array $additionalNotes = null,
        /** @var list<Concept>|null */
        public readonly ?array $relatedSkill = null,
        /** @var list<Concept>|null */
        public readonly ?array $relatedEscosSkill = null,
        public readonly ?Concept $reusabilityLevel = null,
    ) {
        parent::__construct($id);
        if (isset($relatedSkill)) {
            ConceptAssertions::assertSchemes(
                $relatedSkill,
                ElmVocabularySchemes::DCF_SKILLS,
                'relatedSkill',
            );
        }
        if (isset($relatedEscosSkill)) {
            ConceptAssertions::assertSchemes(
                $relatedEscosSkill,
                ElmVocabularySchemes::ESCO_SKILLS,
                'relatedEscosSkill',
            );
        }
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
            'id' => $this->id,
            'type' => 'LearningOutcome',
            'title' => $this->title->toArray(),
        ];
        if ($this->additionalNotes !== null) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNotes,
            );
        }
        if ($this->relatedSkill !== null) {
            $data['relatedSkill'] = array_map(
                static fn (Concept $relatedSkill): array => $relatedSkill->toArray(),
                $this->relatedSkill,
            );
        }
        if ($this->relatedEscosSkill !== null) {
            $data['relatedESCOSkill'] = array_map(
                static fn (Concept $skill): array => $skill->toArray(),
                $this->relatedEscosSkill,
            );
        }
        if ($this->reusabilityLevel !== null) {
            $data['reusabilityLevel'] = $this->reusabilityLevel->toArray();
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'LearningOutcome') {
            throw new InvalidCredentialException('Data is not a LearningOutcome.');
        }
        if (!isset($data['id'])) {
            throw new InvalidCredentialException('A learning outcome requires an id.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('A learning outcome requires a title.');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            isset($data['additionalNote']) ? array_map(
                static fn (array $note): Note => Note::fromArray($note),
                $data['additionalNote'],
            ) : null,
            isset($data['relatedSkill']) ? array_map(
                static fn (array $skill): Concept => Concept::fromArray($skill),
                $data['relatedSkill'],
            ) : null,
            isset($data['relatedESCOSkill']) ? array_map(
                static fn (array $skill): Concept => Concept::fromArray($skill),
                $data['relatedESCOSkill'],
            ) : null,
            isset($data['reusabilityLevel']) ? Concept::fromArray($data['reusabilityLevel']) : null,
        );
    }
}
