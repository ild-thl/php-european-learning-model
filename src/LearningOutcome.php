<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class LearningOutcome extends Entity
{
    /** @param list<Concept> $relatedSkills */
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly array $relatedSkills = [],
    ) {
        parent::__construct($id);
        if (array_filter(
            $relatedSkills,
            static fn ($relatedSkill): bool => !$relatedSkill instanceof Concept,
        ) !== []) {
            throw new \InvalidArgumentException('Learning outcome skills must be concepts.');
        }
    }

    public function toArray(): array
    {
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
        return $data;
    }
}