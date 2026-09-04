<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class DisplayParameter extends Entity
{
    public function __construct(
        string $id,
        public readonly Concept $language,
        public readonly Concept $primaryLanguage,
        public readonly LocalizedString $title,
        public readonly ?LocalizedString $description = null,
    ) {
        parent::__construct($id);
    }

    public function toArray(): array
    {
        $data = [
            'id' => 'urn:epass:displayParameter:' . $this->id,
            'type' => 'DisplayParameter',
            'primaryLanguage' => $this->primaryLanguage->toArray(),
            'language' => $this->language->toArray(),
            'title' => $this->title->toArray(),
        ];

        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }

        return $data;
    }
}