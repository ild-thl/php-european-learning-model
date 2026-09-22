<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Concept extends Entity {

    public function __construct(
        string $id,
        public readonly ?LocalizedString $prefLabel = null,
        public readonly ?ConceptScheme $inScheme = null,
        public readonly ?string $notation = null,
        public readonly ?LocalizedString $definition = null,
        public readonly ?LocalizedString $altLabel = null,
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
        ];
        if ($this->inScheme !== null) {
            $data['inScheme'] = $this->inScheme->toArray();
        }
        if ($this->prefLabel !== null) {
            $data['prefLabel'] = $this->prefLabel->toArray();
        }
        if ($this->notation !== null) {
            $data['notation'] = $this->notation;
        }
        if ($this->definition !== null) {
            $data['definition'] = $this->definition->toArray();
        }
        if ($this->altLabel !== null) {
            $data['altLabel'] = $this->altLabel->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Concept') {
            throw new InvalidCredentialException('Data is not a Concept.');
        }

        return new self(
            $data['id'],
            isset($data['prefLabel']) ? LocalizedString::fromArray($data['prefLabel']) : null,
            isset($data['inScheme']) ? ConceptScheme::fromArray($data['inScheme']) : null,
            isset($data['notation']) ? $data['notation'] : null,
            isset($data['definition']) ? LocalizedString::fromArray($data['definition']) : null,
            isset($data['altLabel']) ? LocalizedString::fromArray($data['altLabel']) : null,
        );
    }
}
