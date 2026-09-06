<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core\Validation;

use InvalidArgumentException;

final class InMemoryProfileResourceRegistry implements ProfileResourceRegistryInterface {

    /** @param array<string, string> $resources */
    public function __construct(private readonly array $resources) {
    }

    public function get(string $profileResource): string {
        if (!array_key_exists($profileResource, $this->resources)) {
            throw new InvalidArgumentException(sprintf(
                'Profile resource "%s" is not registered.',
                $profileResource,
            ));
        }

        return $this->resources[$profileResource];
    }
}
