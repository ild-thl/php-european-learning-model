<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class IndividualDisplay extends Entity {

    /** @param list<DisplayDetail> $displayDetails */
    public function __construct(
        string $id,
        public readonly Concept $language,
        public readonly array $displayDetails,
    ) {
        parent::__construct($id);
        if (
            $displayDetails === [] || array_filter(
                $displayDetails,
                static fn ($displayDetail): bool => !$displayDetail instanceof DisplayDetail,
            ) !== []
        ) {
            throw new InvalidCredentialException('An individual display requires display details.');
        }
    }

    public function toArray(): array {
        return [
            'id' => 'urn:epass:individualDisplay:' . $this->id,
            'type' => 'IndividualDisplay',
            'displayDetail' => array_map(
                static fn (DisplayDetail $displayDetail): array => $displayDetail->toArray(),
                $this->displayDetails,
            ),
            'language' => $this->language->toArray(),
        ];
    }
}
