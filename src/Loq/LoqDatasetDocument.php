<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\Qualification;
use IsyThl\EuropeanLearningModel\Core\JsonLdEncoder;

final class LoqDatasetDocument {

    /** @param list<Qualification|LearningOpportunity> $roots */
    public function __construct(
        private readonly array $roots,
        private readonly LoqProfileValidator $validator = new LoqProfileValidator(),
    ) {
        if ($roots === []) {
            throw new InvalidCredentialException('A LOQ dataset requires at least one root.');
        }
        foreach ($roots as $root) {
            if ($root instanceof Qualification) {
                $this->validator->validateQualification($root);
                continue;
            }
            if ($root instanceof LearningOpportunity) {
                $this->validator->validateLearningOpportunity($root);
                continue;
            }
            throw new InvalidCredentialException('LOQ dataset roots must be typed qualifications or opportunities.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            '@context' => 'http://data.europa.eu/snb/model/elm/',
            '@graph' => array_map(
                static fn (Qualification|LearningOpportunity $root): array => $root->toArray(),
                $this->roots,
            ),
        ];
    }

    public function toJson(): string {
        return JsonLdEncoder::encode($this->toArray());
    }
}
