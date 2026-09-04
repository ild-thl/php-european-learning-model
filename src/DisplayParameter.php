<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class DisplayParameter extends Entity
{
    public function __construct(
        string $id,
        public readonly Concept $language,
        public readonly Concept $primaryLanguage,
        public readonly LocalizedString $title,
        public readonly ?LocalizedString $description = null,
        /** @var list<IndividualDisplay> */
        public readonly array $individualDisplays = [],
    ) {
        parent::__construct($id);
        if (array_filter(
            $individualDisplays,
            static fn ($individualDisplay): bool => !$individualDisplay instanceof IndividualDisplay,
        ) !== []) {
            throw new InvalidCredentialException('Display parameters accept only individual displays.');
        }
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

        if ($this->individualDisplays !== []) {
            $data['individualDisplay'] = array_map(
                static fn (IndividualDisplay $individualDisplay): array => $individualDisplay->toArray(),
                $this->individualDisplays,
            );
        }

        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }

        return $data;
    }
}