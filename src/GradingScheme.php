<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class GradingScheme extends Entity {

    public function __construct(
        string $id,
        public readonly LocalizedString $description,
        public readonly LocalizedString $title,
    ) {
        parent::__construct($id);
    }

    public function toArray(): array {
        return [
            'id' => 'urn:epass:gradingScheme:' . $this->id,
            'type' => 'GradingScheme',
            'description' => $this->description->toArray(),
            'title' => $this->title->toArray(),
        ];
    }
}
