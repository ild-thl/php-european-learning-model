<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Core\JsonLdEncoder;

final class QualificationDocument {

    /** @param list<Qualification> $qualifications */
    public function __construct(
        private readonly array $qualifications,
        private readonly LoqProfileValidator $validator = new LoqProfileValidator(),
    ) {
        if ($qualifications === []) {
            throw new InvalidCredentialException('A qualification document requires at least one root.');
        }
        foreach ($qualifications as $qualification) {
            if (!$qualification instanceof Qualification) {
                throw new InvalidCredentialException('Qualification document roots must be typed qualifications.');
            }
            $this->validator->validateQualification($qualification);
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            '@context' => 'http://data.europa.eu/snb/model/elm/',
            '@graph' => array_map(
                static fn (Qualification $qualification): array => $qualification->toArray(),
                $this->qualifications,
            ),
        ];
    }

    public function toJson(): string {
        return JsonLdEncoder::encode($this->toArray());
    }
}
