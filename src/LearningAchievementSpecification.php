<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class LearningAchievementSpecification extends Entity
{
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly ?LocalizedString $description = null,
        /** @var list<CreditPoint> */
        public readonly array $creditPoints = [],
    ) {
        parent::__construct($id);
        if (array_filter(
            $creditPoints,
            static fn ($creditPoint): bool => !$creditPoint instanceof CreditPoint,
        ) !== []) {
            throw new \InvalidArgumentException('Specifications accept only credit points.');
        }
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
        if ($this->creditPoints !== []) {
            $data['creditPoint'] = array_map(
                static fn (CreditPoint $creditPoint): array => $creditPoint->toArray(),
                $this->creditPoints,
            );
        }
        return $data;
    }
}