<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

final class InMemoryVocabularyCache implements VocabularyCache {

    public function __construct(private readonly Clock $clock = new SystemClock()) {
    }

    /** @var array<string, array{scheme: VocabularyScheme, expiresAt: int}> */
    private array $entries = [];

    public function get(string $key): ?VocabularyScheme {
        $entry = $this->entries[$key] ?? null;
        if ($entry === null) {
            return null;
        }
        if ($entry['expiresAt'] <= $this->clock->now()) {
            unset($this->entries[$key]);
            return null;
        }

        return $entry['scheme'];
    }

    public function set(string $key, VocabularyScheme $scheme, int $ttlSeconds): void {
        $this->entries[$key] = [
            'scheme' => $scheme,
            'expiresAt' => $this->clock->now() + $ttlSeconds,
        ];
    }
}
