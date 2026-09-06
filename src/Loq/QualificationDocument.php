<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\Qualification;
use IsyThl\EuropeanLearningModel\Core\JsonLdEncoder;

final class QualificationDocument {

    /** @param list<Qualification> $qualifications */
    public function __construct(private readonly array $qualifications) {
        if ($qualifications === []) {
            throw new InvalidCredentialException('A qualification document requires at least one root.');
        }
        foreach ($qualifications as $qualification) {
            if (!$qualification instanceof Qualification) {
                throw new InvalidCredentialException('Qualification document roots must be typed qualifications.');
            }
            self::validate($qualification);
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

    private static function validate(Qualification $qualification): void {
        if ($qualification->eqfLevel === null) {
            throw new InvalidCredentialException('A LOQ qualification requires one EQF level.');
        }
        if ($qualification->nqfLevels === []) {
            throw new InvalidCredentialException('A LOQ qualification requires at least one NQF level.');
        }
        if ($qualification->educationSubjects === []) {
            throw new InvalidCredentialException('A LOQ qualification requires at least one ISCED-F subject.');
        }
        if ($qualification->learningOutcomes === []) {
            throw new InvalidCredentialException('A LOQ qualification requires at least one learning outcome.');
        }
    }
}
