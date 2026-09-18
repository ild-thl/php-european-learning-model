<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Support;

use IsyThl\EuropeanLearningModel\Core\JsonLdEncoder;

trait SerializationAssertions {

    /** @param array<string, mixed> $expected */
    protected function assertArrayRoundTrip(array $expected, callable $deserialize): void {
        $entity = $deserialize($expected);

        self::assertSame($expected, $entity->toArray());
    }

    /**
     * @param array<string, mixed> $expected
     * @param callable(array<string, mixed>): object $deserialize
     * @param callable(object): array<string, mixed> $serialize
     */
    protected function assertJsonRoundTrip(
        array $expected,
        callable $deserialize,
        callable $serialize,
    ): void {
        $json = JsonLdEncoder::encode($expected);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($expected, $decoded);
        self::assertSame($expected, $serialize($deserialize($decoded)));
    }
}
