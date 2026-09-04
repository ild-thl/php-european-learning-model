<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\CachedVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\InMemoryVocabularyCache;
use IsyThl\EuropeanDigitalCredentials\InMemoryVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\JsonLdVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use IsyThl\EuropeanDigitalCredentials\RdfVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\VocabularyResourceFetcher;
use IsyThl\EuropeanDigitalCredentials\VocabularyScheme;
use IsyThl\EuropeanDigitalCredentials\VocabularyProvider;
use PHPUnit\Framework\TestCase;

final class VocabularyTest extends TestCase {

    public function testOfflineProviderEnumeratesAndLooksUpAllowedConcepts(): void {
        $scheme = new ConceptScheme('http://example.test/scheme');
        $concept = new Concept(
            'http://example.test/concept/one',
            new LocalizedString(['en' => 'One', 'de' => 'Eins']),
            $scheme,
            'one',
        );
        $snapshot = new VocabularyScheme(
            $scheme->id,
            $scheme,
            [$concept],
            new LocalizedString(['en' => 'Example scheme']),
            'local-test-resource',
        );
        $provider = new InMemoryVocabularyProvider([$snapshot]);

        self::assertSame([$concept], $provider->getScheme($scheme->id)?->concepts);
        self::assertSame($concept, $provider->getConcept($concept->id, $scheme->id));
        self::assertSame($concept, $provider->getConceptByNotation('one', $scheme->id));
        self::assertTrue($snapshot->contains($concept));
        self::assertSame('Eins', $snapshot->toArray()['concept'][0]['prefLabel']['de'][0]);
    }

    public function testProfileSchemeRegistryContainsStableIdentifiers(): void {
        self::assertSame(
            'http://publications.europa.eu/resource/authority/language',
            ElmVocabularySchemes::LANGUAGE,
        );
        self::assertSame(
            'http://data.europa.eu/snb/credential/25831c2',
            ElmVocabularySchemes::CREDENTIAL,
        );
        self::assertSame(
            'http://data.europa.eu/snb/assessment/25831c2',
            ElmVocabularySchemes::ASSESSMENT,
        );
    }

    public function testVocabularyRejectsConceptFromAnotherScheme(): void {
        $scheme = new ConceptScheme('http://example.test/scheme');
        $otherScheme = new ConceptScheme('http://example.test/other-scheme');

        $this->expectException(InvalidCredentialException::class);

        new VocabularyScheme(
            $scheme->id,
            $scheme,
            [new Concept(
                'http://example.test/concept/one',
                new LocalizedString(['en' => 'One']),
                $otherScheme,
            )],
        );
    }

    public function testVocabularyRejectsDuplicateConcepts(): void {
        $scheme = new ConceptScheme('http://example.test/scheme');
        $concept = new Concept(
            'http://example.test/concept/one',
            new LocalizedString(['en' => 'One']),
            $scheme,
        );

        $this->expectException(InvalidCredentialException::class);

        new VocabularyScheme($scheme->id, $scheme, [$concept, $concept]);
    }

    public function testJsonLdProviderParsesBrowseableScheme(): void {
        $schemeId = 'http://example.test/scheme';
        $fetcher = new class implements VocabularyResourceFetcher {
            public function fetch(string $resource): string {
                return json_encode([
                    '@graph' => [
                        ['@id' => $resource, '@type' => 'ConceptScheme', 'prefLabel' => ['en' => ['Example']]],
                        [
                            '@id' => 'http://example.test/concept/one',
                            '@type' => 'Concept',
                            'inScheme' => ['@id' => $resource],
                            'prefLabel' => ['en' => ['One']],
                            'notation' => 'one',
                        ],
                    ],
                ], JSON_THROW_ON_ERROR);
            }
        };
        $provider = new JsonLdVocabularyProvider($fetcher);

        $scheme = $provider->getScheme($schemeId);

        self::assertNotNull($scheme);
        self::assertSame('One', $scheme->concepts[0]->prefLabel->toArray()['en'][0]);
        self::assertSame('one', $provider->getConcept('http://example.test/concept/one', $schemeId)?->notation);
    }

    public function testRdfProviderParsesBrowseableScheme(): void {
        $schemeId = 'http://example.test/scheme';
        $fetcher = new class implements VocabularyResourceFetcher {
            public function fetch(string $resource): string {
                return <<<XML
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:skos="http://www.w3.org/2004/02/skos/core#"
    xmlns:xml="http://www.w3.org/XML/1998/namespace">
    <skos:ConceptScheme rdf:about="{$resource}">
        <skos:prefLabel xml:lang="en">Example</skos:prefLabel>
    </skos:ConceptScheme>
    <skos:Concept rdf:about="http://example.test/concept/one">
        <skos:inScheme rdf:resource="{$resource}"/>
        <skos:prefLabel xml:lang="en">One</skos:prefLabel>
        <skos:prefLabel xml:lang="de">Eins</skos:prefLabel>
        <skos:notation>one</skos:notation>
    </skos:Concept>
</rdf:RDF>
XML;
            }
        };
        $provider = new RdfVocabularyProvider($fetcher);

        $scheme = $provider->getScheme($schemeId);

        self::assertNotNull($scheme);
        self::assertSame('Example', $scheme->title?->toArray()['en'][0]);
        self::assertSame('Eins', $scheme->concepts[0]->prefLabel->toArray()['de'][0]);
        self::assertSame('one', $scheme->concepts[0]->notation);
    }

    public function testRdfProviderRejectsMalformedXml(): void {
        $fetcher = new class implements VocabularyResourceFetcher {
            public function fetch(string $resource): string {
                return '<rdf:RDF';
            }
        };
        $provider = new RdfVocabularyProvider($fetcher);

        $this->expectException(InvalidCredentialException::class);

        $provider->getScheme('http://example.test/scheme');
    }

    public function testCachedProviderUsesTheCachedScheme(): void {
        $schemeId = 'http://example.test/scheme';
        $scheme = new VocabularyScheme($schemeId, new ConceptScheme($schemeId));
        $source = new InMemoryVocabularyProvider([$scheme]);
        $cached = new CachedVocabularyProvider($source, new InMemoryVocabularyCache());

        self::assertSame($scheme, $cached->getScheme($schemeId));
        self::assertSame($scheme, $cached->getScheme($schemeId));
    }

    public function testCachedProviderLoadsTheSourceOnlyOnCacheMiss(): void {
        $schemeId = 'http://example.test/scheme';
        $scheme = new VocabularyScheme($schemeId, new ConceptScheme($schemeId));
        $source = new class ($scheme) implements VocabularyProvider {
            public int $calls = 0;

            public function __construct(private readonly VocabularyScheme $scheme) {
            }

            public function getScheme(string $schemeId): ?VocabularyScheme {
                $this->calls++;

                return $schemeId === $this->scheme->id ? $this->scheme : null;
            }

            public function getConcept(string $conceptId, string $schemeId): ?Concept {
                return $this->getScheme($schemeId)?->find($conceptId);
            }

            public function getConceptByNotation(string $notation, string $schemeId): ?Concept {
                return $this->getScheme($schemeId)?->findByNotation($notation);
            }
        };
        $cached = new CachedVocabularyProvider($source, new InMemoryVocabularyCache());

        $cached->getScheme($schemeId);
        $cached->getScheme($schemeId);

        self::assertSame(1, $source->calls);
    }

    public function testJsonLdProviderRejectsOversizedResponse(): void {
        $fetcher = new class implements VocabularyResourceFetcher {
            public function fetch(string $resource): string {
                return '{}';
            }
        };
        $provider = new JsonLdVocabularyProvider($fetcher, 1);

        $this->expectException(InvalidCredentialException::class);

        $provider->getScheme('http://example.test/scheme');
    }
}
