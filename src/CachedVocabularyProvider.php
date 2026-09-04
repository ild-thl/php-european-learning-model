<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class CachedVocabularyProvider implements VocabularyProvider {

    public function __construct(
        private readonly VocabularyProvider $provider,
        private readonly VocabularyCache $cache,
        private readonly int $ttlSeconds = 3600,
    ) {
        if ($ttlSeconds < 1) {
            throw new \InvalidArgumentException('Vocabulary cache TTL must be positive.');
        }
    }

    public function getScheme(string $schemeId): ?VocabularyScheme {
        $cached = $this->cache->get($schemeId);
        if ($cached !== null) {
            return $cached;
        }
        $scheme = $this->provider->getScheme($schemeId);
        if ($scheme !== null) {
            $this->cache->set($schemeId, $scheme, $this->ttlSeconds);
        }

        return $scheme;
    }

    public function getConcept(string $conceptId, string $schemeId): ?Concept {
        return $this->getScheme($schemeId)?->find($conceptId);
    }

    public function getConceptByNotation(string $notation, string $schemeId): ?Concept {
        return $this->getScheme($schemeId)?->findByNotation($notation);
    }
}
