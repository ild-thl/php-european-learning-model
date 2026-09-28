<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanLearningModel\Core\Entity;
use IsyThl\EuropeanLearningModel\Core\MediaObject;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class DisplayDetail extends Entity {
    public function __construct(
        string $id,
        public readonly int $page,
        public readonly MediaObject $image,
    ) {
        parent::__construct($id);
        if ($page < 1) {
            throw new InvalidCredentialException('A display detail page must be positive.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'type' => 'DisplayDetail',
            'image' => $this->image->toArray(),
            'page' => $this->page,
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            $data['page'],
            MediaObject::fromArray($data['image']),
        );
    }
}
