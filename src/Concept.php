<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Concept extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(
        string $id,
        public readonly LocalizedString $prefLabel,
        public readonly ConceptScheme $inScheme,
        public readonly ?string $notation = null,
    ) {
        parent::__construct($id);
        if ($notation === '') {
            throw new InvalidCredentialException('A concept notation cannot be empty.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'Concept',
            'inScheme' => $this->inScheme->toArray(),
            'prefLabel' => $this->prefLabel->toArray(),
        ];
        if ($this->notation !== null) {
            $data['notation'] = $this->notation;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (
            !isset($data['id'], $data['inScheme'], $data['prefLabel'])
            || !is_string($data['id'])
            || !is_array($data['inScheme'])
            || !is_array($data['prefLabel'])
        ) {
            throw new InvalidCredentialException('A concept requires id, inScheme, and prefLabel fields.');
        }

        return new self(
            $data['id'],
            new LocalizedString($data['prefLabel']),
            ConceptScheme::fromArray($data['inScheme']),
            $data['notation'] ?? null,
        );
    }
}
