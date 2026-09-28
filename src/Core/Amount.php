<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Amount extends Entity {
    public function __construct(
        string $id,
        public readonly Concept $unit,
        public readonly string $value,
        public readonly ?int $order = null,
    ) {
        parent::__construct($id);

        if (preg_match('/^[+-]?(?:\d+(?:\.\d*)?|\.\d+)$/', $value) !== 1) {
            throw new InvalidCredentialException('Amount values must use XML Schema decimal syntax.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'Amount',
            'unit' => $this->unit->toArray(),
            'value' => $this->value,
        ];
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Grant') {
            throw new InvalidCredentialException('Data is not an Grant.');
        }
        if (!isset($data['unit'])) {
            throw new InvalidCredentialException('Data is missing unit.');
        }
        if (!isset($data['value'])) {
            throw new InvalidCredentialException('Data is missing value.');
        }

        return new self(
            id: $data['id'],
            unit: Concept::fromArray($data['unit']),
            value: $data['value'],
            order: $data['order'] ?? null,
        );
    }
}
