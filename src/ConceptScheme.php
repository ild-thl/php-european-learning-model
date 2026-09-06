<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class ConceptScheme extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(string $id, public readonly string $schemeType = 'ConceptScheme') {
        parent::__construct($id);
        if ($id === '' || $schemeType === '') {
            throw new InvalidCredentialException('A concept scheme requires an identifier and type.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return ['id' => $this->id, 'type' => $this->schemeType];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['id'], $data['type']) || !is_string($data['id']) || !is_string($data['type'])) {
            throw new InvalidCredentialException('A concept scheme requires string id and type fields.');
        }

        return new self($data['id'], $data['type']);
    }
}
