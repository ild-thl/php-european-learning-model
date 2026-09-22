<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

abstract class Entity {

    public readonly string $id;

    public function __construct(?string $id = null) {
        $this->id = $id ?? self::newIdentifier();
    }

    /** @return array<string, mixed> */
    abstract public function toArray(): array;

    /** @param array<string, mixed> $data */
    abstract public static function fromArray(array $data): self;

    public function toJson(): string {
        return JsonLdEncoder::encode($this->toArray());
    }

    private static function newIdentifier(): string {
        try {
            return 'urn:credential:' . bin2hex(random_bytes(16));
        } catch (\Throwable $exception) {
            throw new \JsonException('Unable to create a credential identifier.', 0, $exception);
        }
    }
}
