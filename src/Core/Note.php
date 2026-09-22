<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Note extends Entity {

    public function __construct(
        string $id,
        public readonly LocalizedString $noteLiteral,
        public readonly ?LocalizedString $subject = null,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'Note',
            'noteLiteral' => $this->noteLiteral->toArray(),
        ];

        if ($this->subject !== null) {
            $data['subject'] = $this->subject->toArray();
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Note') {
            throw new InvalidCredentialException('Data is not a Note.');
        }

        return new self(
            $data['id'],
            isset($data['noteLiteral']) ? LocalizedString::fromArray($data['noteLiteral']) : null,
            isset($data['subject']) ? LocalizedString::fromArray($data['subject']) : null,
        );
    }
}
