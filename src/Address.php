<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

final class Address extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(
        string $id,
        public readonly Concept $countryCode,
        public readonly Note $fullAddress,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($countryCode, ElmVocabularySchemes::COUNTRY, 'countryCode');
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => 'urn:epass:address:' . $this->id,
            'type' => 'Address',
            'countryCode' => $this->countryCode->toArray(),
            'fullAddress' => $this->fullAddress->toArray(),
        ];
    }
}
