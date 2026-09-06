<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use JsonException;

final class JsonLdEncoder {

    /**
     * @param array<string, mixed> $document
     * @throws JsonException
     */
    public static function encode(array $document): string {
        return json_encode(
            $document,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}