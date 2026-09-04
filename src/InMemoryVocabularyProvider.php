<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class InMemoryVocabularyProvider implements VocabularyProvider {

    /** @var array<string, VocabularyScheme> */
    private array $schemes = [];

    /**
     * @param list<VocabularyScheme> $schemes
     */
    public function __construct(array $schemes = []) {
        foreach ($schemes as $scheme) {
            if (!$scheme instanceof VocabularyScheme) {
                throw new InvalidCredentialException('Vocabulary providers accept VocabularyScheme objects.');
            }
            if (isset($this->schemes[$scheme->id])) {
                throw new InvalidCredentialException('A vocabulary provider cannot contain duplicate schemes.');
            }
            $this->schemes[$scheme->id] = $scheme;
        }
    }

    public function getScheme(string $schemeId): ?VocabularyScheme {
        return $this->schemes[$schemeId] ?? null;
    }

    public function getConcept(string $conceptId, string $schemeId): ?Concept {
        return $this->schemes[$schemeId]?->find($conceptId);
    }

    public function getConceptByNotation(string $notation, string $schemeId): ?Concept {
        return $this->schemes[$schemeId]?->findByNotation($notation);
    }
}
