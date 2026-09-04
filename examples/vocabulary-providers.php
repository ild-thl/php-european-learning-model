<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IsyThl\EuropeanDigitalCredentials\CachedVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\InMemoryVocabularyCache;
use IsyThl\EuropeanDigitalCredentials\JsonLdVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\RdfVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\VocabularyResourceFetcher;

$schemeId = ElmVocabularySchemes::LANGUAGE;
$languageId = $schemeId . '/ENG';
$jsonLdFetcher = new class ($schemeId, $languageId) implements VocabularyResourceFetcher {
    public function __construct(
        private readonly string $schemeId,
        private readonly string $languageId,
    ) {
    }

    public function fetch(string $resource): string {
        return json_encode([
            '@graph' => [
                ['@id' => $this->schemeId, '@type' => 'ConceptScheme'],
                [
                    '@id' => $this->languageId,
                    '@type' => 'Concept',
                    'inScheme' => ['@id' => $this->schemeId],
                    'prefLabel' => ['en' => ['English']],
                    'notation' => 'ENG',
                ],
            ],
        ], JSON_THROW_ON_ERROR);
    }
};
$jsonLdProvider = new CachedVocabularyProvider(
    new JsonLdVocabularyProvider($jsonLdFetcher),
    new InMemoryVocabularyCache(),
);
$languageFromJsonLd = $jsonLdProvider->getConceptByNotation('ENG', $schemeId);

$rdfFetcher = new class ($schemeId, $languageId) implements VocabularyResourceFetcher {
    public function __construct(
        private readonly string $schemeId,
        private readonly string $languageId,
    ) {
    }

    public function fetch(string $resource): string {
        return <<<XML
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:skos="http://www.w3.org/2004/02/skos/core#"
    xmlns:xml="http://www.w3.org/XML/1998/namespace">
    <skos:ConceptScheme rdf:about="{$this->schemeId}"/>
    <skos:Concept rdf:about="{$this->languageId}">
        <skos:inScheme rdf:resource="{$this->schemeId}"/>
        <skos:prefLabel xml:lang="en">English</skos:prefLabel>
        <skos:notation>ENG</skos:notation>
    </skos:Concept>
</rdf:RDF>
XML;
    }
};
$rdfProvider = new RdfVocabularyProvider($rdfFetcher);
$languageFromRdf = $rdfProvider->getConcept($languageId, $schemeId);

if ($languageFromJsonLd === null || $languageFromRdf === null) {
    throw new RuntimeException('The example vocabulary did not contain English.');
}

echo $languageFromJsonLd->id . PHP_EOL;
echo $languageFromRdf->prefLabel->toArray()['en'][0] . PHP_EOL;
