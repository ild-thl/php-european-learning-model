<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Address extends Entity {

    public function __construct(
        string $id,
        public readonly Concept $countryCode,
        public readonly ?Note $fullAddress = null,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($countryCode, ElmVocabularySchemes::COUNTRY, 'countryCode');
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'Address',
            'countryCode' => $this->countryCode->toArray(),
        ];

        if ($this->fullAddress !== null) {
            $data['fullAddress'] = $this->fullAddress->toArray();
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Address') {
            throw new InvalidCredentialException('Data is not an Address.');
        }

        return new self(
            $data['id'],
            isset($data['countryCode']) ? Concept::fromArray($data['countryCode']) : null,
            isset($data['fullAddress']) ? Note::fromArray($data['fullAddress']) : null,
        );
    }
}
