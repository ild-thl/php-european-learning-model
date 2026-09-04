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
use IsyThl\EuropeanDigitalCredentials\VocabularyResourceFetcher;
use IsyThl\EuropeanDigitalCredentials\VocabularyScheme;
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
        self::assertTrue($snapshot->contains($concept));
        self::assertSame('Eins', $snapshot->toArray()['concept'][0]['prefLabel']['de'][0]);
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

    public function testCachedProviderUsesTheCachedScheme(): void {
        $schemeId = 'http://example.test/scheme';
        $scheme = new VocabularyScheme($schemeId, new ConceptScheme($schemeId));
        $source = new InMemoryVocabularyProvider([$scheme]);
        $cached = new CachedVocabularyProvider($source, new InMemoryVocabularyCache());

        self::assertSame($scheme, $cached->getScheme($schemeId));
        self::assertSame($scheme, $cached->getScheme($schemeId));
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
