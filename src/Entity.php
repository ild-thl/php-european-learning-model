<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use JsonException;

abstract class Entity {

    public readonly string $id;

    public function __construct(?string $id = null) {
        $this->id = $id ?? self::newIdentifier();
    }

    /** @return array<string, mixed> */
    abstract public function toArray(): array;

    public function toJson(): string {
        return json_encode(
            $this->toArray(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    private static function newIdentifier(): string {
        try {
            return 'urn:credential:' . bin2hex(random_bytes(16));
        } catch (\Throwable $exception) {
            throw new JsonException('Unable to create a credential identifier.', 0, $exception);
        }
    }
}
