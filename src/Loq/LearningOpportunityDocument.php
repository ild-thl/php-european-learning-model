<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Core\JsonLdEncoder;

final class LearningOpportunityDocument {

    /** @param list<LearningOpportunity> $opportunities */
    public function __construct(
        private readonly array $opportunities,
        private readonly LoqProfileValidator $validator = new LoqProfileValidator(),
    ) {
        if ($opportunities === []) {
            throw new InvalidCredentialException('A learning opportunity document requires at least one root.');
        }
        foreach ($opportunities as $opportunity) {
            if (!$opportunity instanceof LearningOpportunity) {
                throw new InvalidCredentialException('Learning opportunity document roots must be typed objects.');
            }
            $this->validator->validateLearningOpportunity($opportunity);
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            '@context' => 'http://data.europa.eu/snb/model/elm/',
            '@graph' => array_map(
                static fn (LearningOpportunity $opportunity): array => $opportunity->toArray(),
                $this->opportunities,
            ),
        ];
    }

    public function toJson(): string {
        return JsonLdEncoder::encode($this->toArray());
    }
}
