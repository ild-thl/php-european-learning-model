<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class PriceDetail extends Entity {
    public function __construct(
        string $id,
        /** @var list<Identifier|LegalIdentifier>|null */
        public readonly ?array $identifier = null,
        public readonly ?LocalizedString $prefLabel = null,
        public readonly ?Amount $amount = null,
        public readonly ?LocalizedString $description = null,
        /** @var list<Note>|null */
        public readonly ?array $additionalNotes = null,
        public readonly ?int $order = null,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'PriceDetail',
        ];
        if ($this->identifier !== null) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifier,
            );
        }
        if ($this->prefLabel !== null) {
            $data['prefLabel'] = $this->prefLabel->toArray();
        }
        if ($this->amount !== null) {
            $data['amount'] = $this->amount->toArray();
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
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'PriceDetail') {
            throw new InvalidCredentialException('Data is not an PriceDetail.');
        }

        return new self(
            $data['id'],
            isset($data['identifier']) ? array_map(
                static fn (array $identifier): Identifier|LegalIdentifier => Identifier::fromArray($identifier),
                $data['identifier'],
            ) : null,
            isset($data['prefLabel']) ? LocalizedString::fromArray($data['prefLabel']) : null,
            isset($data['amount']) ? Amount::fromArray($data['amount']) : null,
            isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            isset($data['additionalNote']) ? array_map(
                static fn (array $note): Note => Note::fromArray($note),
                $data['additionalNote'],
            ) : [],
            $data['order'] ?? null,
        );
    }
}
