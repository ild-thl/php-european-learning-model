<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use IsyThl\EuropeanDigitalCredentials\Note;

final class PriceDetail {

    /** @param list<Note> $additionalNotes */
    public function __construct(
        public readonly ?LocalizedString $prefLabel = null,
        public readonly ?LocalizedString $description = null,
        public readonly array $additionalNotes = [],
        public readonly ?Amount $amount = null,
    ) {
        if (array_filter($additionalNotes, static fn ($note): bool => !$note instanceof Note) !== []) {
            throw new \InvalidArgumentException('Price detail notes must be Note objects.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = ['type' => 'PriceDetail'];
        if ($this->prefLabel !== null) {
            $data['prefLabel'] = $this->prefLabel->toArray();
        }
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->additionalNotes !== []) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNotes,
            );
        }
        if ($this->amount !== null) {
            $data['amount'] = $this->amount->toArray();
        }
        return $data;
    }
}
