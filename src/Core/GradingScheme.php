<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

final class GradingScheme extends Entity {
    public function __construct(
        string $id,
        public readonly LocalizedString $description,
        public readonly LocalizedString $title,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'type' => 'GradingScheme',
            'description' => $this->description->toArray(),
            'title' => $this->title->toArray(),
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            LocalizedString::fromArray($data['description']),
            LocalizedString::fromArray($data['title']),
        );
    }
}
