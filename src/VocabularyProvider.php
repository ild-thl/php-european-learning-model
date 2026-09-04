<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

interface VocabularyProvider {

    public function getScheme(string $schemeId): ?VocabularyScheme;

    public function getConcept(string $conceptId, string $schemeId): ?Concept;

    public function getConceptByNotation(string $notation, string $schemeId): ?Concept;
}
