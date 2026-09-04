<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class InMemoryVocabularyCache implements VocabularyCache {

    /** @var array<string, array{scheme: VocabularyScheme, expiresAt: int}> */
    private array $entries = [];

    public function get(string $key): ?VocabularyScheme {
        $entry = $this->entries[$key] ?? null;
        if ($entry === null) {
            return null;
        }
        if ($entry['expiresAt'] <= time()) {
            unset($this->entries[$key]);
            return null;
        }

        return $entry['scheme'];
    }

    public function set(string $key, VocabularyScheme $scheme, int $ttlSeconds): void {
        $this->entries[$key] = [
            'scheme' => $scheme,
            'expiresAt' => time() + $ttlSeconds,
        ];
    }
}
