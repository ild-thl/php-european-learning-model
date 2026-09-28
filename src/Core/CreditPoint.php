<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class CreditPoint extends Entity {
    public function __construct(
        string $id,
        public readonly Concept $framework,
        public readonly string $point,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($framework, ElmVocabularySchemes::EDUCATION_CREDIT, 'framework');
        if ($point === '') {
            throw new InvalidCredentialException('A credit point requires a point value.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'type' => 'CreditPoint',
            'framework' => $this->framework->toArray(),
            'point' => $this->point,
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'CreditPoint') {
            throw new InvalidCredentialException('Data is not a CreditPoint.');
        }
        if (!isset($data['framework'])) {
            throw new InvalidCredentialException('Data is missing framework');
        }
        if (!isset($data['point'])) {
            throw new InvalidCredentialException('Data is missing point');
        }

        return new self(
            $data['id'],
            Concept::fromArray($data['framework']),
            $data['point'],
        );
    }
}
