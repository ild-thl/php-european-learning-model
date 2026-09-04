<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class LearningOutcome extends Entity {

    /** @param list<Concept> $relatedSkills */
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly array $relatedSkills = [],
        /** @var list<Note> */
        public readonly array $additionalNotes = [],
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
        if (
            array_filter(
                $additionalNotes,
                static fn ($note): bool => !$note instanceof Note,
            ) !== []
        ) {
            throw new InvalidCredentialException('Learning outcome notes must be Note objects.');
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
        if ($this->additionalNotes !== []) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNotes,
            );
        }
        return $data;
    }
}
