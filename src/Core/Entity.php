<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

abstract class Entity {
    /** @var array<int, true> */
    private static array $serializationStack = [];

    public readonly string $id;

    public function __construct(?string $id = null) {
        $this->id = $id ?? self::newIdentifier();
    }

    /** @return array<string, mixed> */
    abstract public function toArray(): array;

    /** @param array<string, mixed> $data */
    abstract public static function fromArray(array $data): self;

    protected function beginSerialization(): void {
        $objectId = spl_object_id($this);
        if (isset(self::$serializationStack[$objectId])) {
            throw new InvalidCredentialException('Circular entity reference detected during serialization.');
        }
        self::$serializationStack[$objectId] = true;
    }

    protected function endSerialization(): void {
        unset(self::$serializationStack[spl_object_id($this)]);
    }

    public function toJson(): string {
        return JsonLdEncoder::encode($this->toArray());
    }

    private static function newIdentifier(): string {
        try {
            return 'urn:epass:' . bin2hex(random_bytes(16));
        } catch (\Throwable $exception) {
            throw new \JsonException('Unable to create a credential identifier.', 0, $exception);
        }
    }
}
