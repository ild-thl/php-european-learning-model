<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class LearningAchievementSpecification extends Entity
{
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly ?LocalizedString $description = null,
    ) {
        parent::__construct($id);
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
        return $data;
    }
}