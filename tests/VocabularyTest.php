<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\CachedVocabularyProvider;
use IsyThl\EuropeanLearningModel\Core\Clock;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\InMemoryVocabularyCache;
use IsyThl\EuropeanLearningModel\InMemoryVocabularyProvider;
use IsyThl\EuropeanLearningModel\JsonLdVocabularyProvider;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\RdfVocabularyProvider;
use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\VocabularyResourceFetcher;
use IsyThl\EuropeanLearningModel\Vocabulary\JsonVocabularySearchProvider;
use IsyThl\EuropeanLearningModel\Vocabulary\EscoVocabularySearchProvider;
use IsyThl\EuropeanLearningModel\Vocabulary\VocabularySearchResourceFetcher;
use IsyThl\EuropeanLearningModel\Core\VocabularyScheme;
use IsyThl\EuropeanLearningModel\VocabularyProvider;
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
        $snapshot->assertContains($concept, 'language');
        self::assertSame('Eins', $snapshot->toArray()['concept'][0]['prefLabel']['de'][0]);
    }

    public function testSchemeProvidesDropdownLookupsAndLanguageAwareSearch(): void {
        $scheme = new ConceptScheme('http://example.test/activities');
        $workshop = new Concept(
            'http://example.test/activity/workshop',
            new LocalizedString(['en' => 'Workshop', 'de' => 'Workshop']),
            $scheme,
            'workshop',
        );
        $lecture = new Concept(
            'http://example.test/activity/lecture',
            new LocalizedString(['en' => 'Lecture', 'de' => 'Vorlesung']),
            $scheme,
            'lecture',
        );
        $snapshot = new VocabularyScheme($scheme->id, $scheme, [$workshop, $lecture]);

        self::assertSame([$workshop, $lecture], $snapshot->getConcepts());
        self::assertSame($workshop, $snapshot->byId($workshop->id));
        self::assertSame($lecture, $snapshot->byNotation('lecture'));
        self::assertSame([$lecture], $snapshot->search('vor', 'de'));
        self::assertSame('Workshop', $workshop->prefLabel->value('fr', ['en']));
    }

    public function testSearchProviderPaginatesWithoutChangingSelectionIdentifiers(): void {
        $scheme = new ConceptScheme('http://example.test/activities');
        $concepts = [];
        foreach (['Workshop', 'Lecture', 'Seminar'] as $index => $label) {
            $concepts[] = new Concept(
                'http://example.test/activity/' . strtolower($label),
                new LocalizedString(['en' => $label]),
                $scheme,
                strtolower($label),
            );
        }
        $provider = new InMemoryVocabularyProvider([new VocabularyScheme($scheme->id, $scheme, $concepts)]);

        $firstPage = $provider->searchConcepts($scheme->id, '', 'en', 2);
        $secondPage = $provider->searchConcepts($scheme->id, '', 'en', 2, $firstPage->nextCursor);

        self::assertSame([$concepts[0], $concepts[1]], $firstPage->concepts);
        self::assertTrue($firstPage->hasMore());
        self::assertSame([$concepts[2]], $secondPage->concepts);
        self::assertFalse($secondPage->hasMore());
        self::assertSame($concepts[2], $provider->getConceptByNotation('seminar', $scheme->id));
    }

    public function testVocabularyPageRejectsEmptyCursor(): void {
        $this->expectExceptionMessage('must not be empty');

        new \IsyThl\EuropeanLearningModel\Vocabulary\VocabularyConceptPage([], '');
    }

    public function testJsonSearchProviderParsesPagedConcepts(): void {
        $schemeId = 'http://example.test/activities';
        $fetcher = new class implements VocabularySearchResourceFetcher {
            public function search(
                string $schemeId,
                string $query,
                string $language,
                int $limit,
                ?string $cursor,
                array $fallbackLanguages = [],
            ): string {
                return json_encode([
                    'concepts' => [
                        [
                            'id' => 'http://example.test/activity/workshop',
                            'type' => 'Concept',
                            'inScheme' => ['id' => $schemeId, 'type' => 'ConceptScheme'],
                            'prefLabel' => [$language => ['Workshop']],
                            'notation' => 'workshop',
                        ],
                    ],
                    'nextCursor' => 'page-2',
                ], JSON_THROW_ON_ERROR);
            }
        };
        $provider = new JsonVocabularySearchProvider($fetcher);

        $page = $provider->searchConcepts($schemeId, 'work', 'en', 1);

        self::assertSame('http://example.test/activity/workshop', $page->concepts[0]->id);
        self::assertSame('page-2', $page->nextCursor);
    }

    public function testJsonSearchProviderRejectsArrayResponse(): void {
        $fetcher = new class implements VocabularySearchResourceFetcher {
            public function search(
                string $schemeId,
                string $query,
                string $language,
                int $limit,
                ?string $cursor,
                array $fallbackLanguages = [],
            ): string {
                return '[]';
            }
        };

        $this->expectExceptionMessage('must be a JSON object');

        (new JsonVocabularySearchProvider($fetcher))->searchConcepts(
            'http://example.test/activities',
            '',
            'en',
        );
    }

    public function testEscoSearchProviderParsesSkillsAndPreservesNextLink(): void {
        $fetcher = new class implements VocabularySearchResourceFetcher {
            public function search(
                string $schemeId,
                string $query,
                string $language,
                int $limit,
                ?string $cursor,
                array $fallbackLanguages = [],
            ): string {
                return json_encode([
                    '_embedded' => [
                        'results' => [[
                            'className' => 'Skill',
                            'uri' => 'http://data.europa.eu/esco/skill/example',
                            'preferredLabel' => ['en' => 'Example skill'],
                            'isInScheme' => [$schemeId, 'http://data.europa.eu/esco/concept-scheme/member-skills'],
                        ]],
                    ],
                    '_links' => ['next' => ['href' => 'https://example.test/esco/search?offset=1']],
                ], JSON_THROW_ON_ERROR);
            }
        };
        $provider = new EscoVocabularySearchProvider($fetcher);

        $page = $provider->searchConcepts(ElmVocabularySchemes::ESCO_SKILLS, 'example', 'en', 1);

        self::assertSame('Example skill', $page->concepts[0]->prefLabel->value('en'));
        self::assertSame('https://example.test/esco/search?offset=1', $page->nextCursor);
        self::assertSame(ElmVocabularySchemes::ESCO_SKILLS, $page->concepts[0]->inScheme->id);
    }

    public function testEscoSearchProviderParsesOccupationLabelsWithRegionalTags(): void {
        $fetcher = new class implements VocabularySearchResourceFetcher {
            public function search(
                string $schemeId,
                string $query,
                string $language,
                int $limit,
                ?string $cursor,
                array $fallbackLanguages = [],
            ): string {
                return json_encode([
                    '_embedded' => [
                        'results' => [[
                            'className' => 'Occupation',
                            'uri' => 'http://data.europa.eu/esco/occupation/example',
                            'preferredLabel' => ['en-us' => 'Example occupation'],
                            'isInScheme' => [$schemeId],
                        ]],
                    ],
                ], JSON_THROW_ON_ERROR);
            }
        };
        $provider = new EscoVocabularySearchProvider($fetcher);

        $page = $provider->searchConcepts(ElmVocabularySchemes::OCCUPATIONS, 'example', 'en', 1);

        self::assertSame('Example occupation', $page->concepts[0]->prefLabel->value('en-US'));
    }

    public function testEscoSearchProviderRejectsUnsupportedSchemes(): void {
        $fetcher = $this->createMock(VocabularySearchResourceFetcher::class);
        $provider = new EscoVocabularySearchProvider($fetcher);

        $this->expectException(InvalidCredentialException::class);
        $provider->searchConcepts(ElmVocabularySchemes::LANGUAGE, 'English', 'en');
    }

    public function testEscoSearchProviderRejectsArrayResponse(): void {
        $fetcher = new class implements VocabularySearchResourceFetcher {
            public function search(
                string $schemeId,
                string $query,
                string $language,
                int $limit,
                ?string $cursor,
                array $fallbackLanguages = [],
            ): string {
                return '[]';
            }
        };

        $this->expectExceptionMessage('must be a JSON object');

        (new EscoVocabularySearchProvider($fetcher))->searchConcepts(
            ElmVocabularySchemes::ESCO_SKILLS,
            '',
            'en',
        );
    }

    public function testVocabularyReportsMembershipFailure(): void {
        $scheme = new ConceptScheme('http://example.test/scheme');
        $snapshot = new VocabularyScheme($scheme->id, $scheme);
        $otherConcept = new Concept(
            'http://example.test/concept/other',
            new LocalizedString(['en' => 'Other']),
            new ConceptScheme('http://example.test/other-scheme'),
        );

        $this->expectExceptionObject(new InvalidCredentialException(
            'country must belong to vocabulary scheme "http://example.test/scheme".',
        ));

        $snapshot->assertContains($otherConcept, 'country');
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
        self::assertCount(23, ElmVocabularySchemes::all());
    }

    public function testEveryRegisteredSchemeHasAnOwnershipClassification(): void {
        $schemes = ElmVocabularySchemes::all();
        $ownership = ElmVocabularySchemes::ownership();

        self::assertSame(array_keys($schemes), array_keys($ownership));
        self::assertNotContains('ACCREDITATION', array_keys($schemes));
        self::assertContains('model-enforced', $ownership);
        self::assertContains('search-backed', $ownership);
    }

    public function testRdfProviderParsesEveryRegisteredSchemeWithTheSameContract(): void {
        $fetcher = new class implements VocabularyResourceFetcher {
            public function fetch(string $resource): string {
                $conceptId = $resource . '/example';

                return <<<XML
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:skos="http://www.w3.org/2004/02/skos/core#"
    xmlns:xml="http://www.w3.org/XML/1998/namespace">
    <skos:ConceptScheme rdf:about="{$resource}"/>
    <skos:Concept rdf:about="{$conceptId}">
        <skos:inScheme rdf:resource="{$resource}"/>
        <skos:prefLabel xml:lang="en">Example</skos:prefLabel>
        <skos:notation>example</skos:notation>
    </skos:Concept>
</rdf:RDF>
XML;
            }
        };
        $provider = new RdfVocabularyProvider($fetcher);

        foreach (ElmVocabularySchemes::all() as $schemeId) {
            $scheme = $provider->getScheme($schemeId);

            self::assertNotNull($scheme, $schemeId);
            self::assertSame('Example', $scheme->byNotation('example')?->prefLabel->value('en'));
        }
    }

    public function testRdfProviderRejectsUnboundedEnrichment(): void {
        $schemeId = 'http://example.test/large-scheme';
        $fetcher = new class ($schemeId) implements VocabularyResourceFetcher {
            public function __construct(private readonly string $schemeId) {
            }

            public function fetch(string $resource): string {
                $concepts = '';
                for ($index = 1; $index <= 2; $index++) {
                    $conceptId = $this->schemeId . '/concept-' . $index;
                    $concepts .= '<rdf:Description rdf:about="' . $conceptId . '">'
                        . '<skos:inScheme rdf:resource="' . $this->schemeId . '"/>'
                        . '</rdf:Description>';
                }

                return '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'
                    . ' xmlns:skos="http://www.w3.org/2004/02/skos/core#">'
                    . '<skos:ConceptScheme rdf:about="' . $this->schemeId . '"/>'
                    . $concepts . '</rdf:RDF>';
            }
        };

        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('use paged search');

        (new RdfVocabularyProvider($fetcher, 5_000_000, 1))->getScheme($schemeId);
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

    public function testRdfProviderEnrichesBareSchemeConceptIds(): void {
        $schemeId = 'http://example.test/scheme';
        $conceptId = 'http://example.test/concept/one';
        $fetcher = new class ($schemeId, $conceptId) implements VocabularyResourceFetcher {
            public function __construct(
                private readonly string $schemeId,
                private readonly string $conceptId,
            ) {
            }

            public function fetch(string $resource): string {
                if ($resource === $this->conceptId) {
                    return <<<XML
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:skos="http://www.w3.org/2004/02/skos/core#"
    xmlns:xml="http://www.w3.org/XML/1998/namespace">
    <rdf:Description rdf:about="{$this->conceptId}">
        <skos:inScheme rdf:resource="{$this->schemeId}"/>
        <skos:prefLabel xml:lang="en">One</skos:prefLabel>
    </rdf:Description>
</rdf:RDF>
XML;
                }

                return <<<XML
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:skos="http://www.w3.org/2004/02/skos/core#">
    <rdf:Description rdf:about="{$this->schemeId}">
        <rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#ConceptScheme"/>
    </rdf:Description>
    <rdf:Description rdf:about="{$this->conceptId}">
        <skos:inScheme rdf:resource="{$this->schemeId}"/>
    </rdf:Description>
</rdf:RDF>
XML;
            }
        };

        $scheme = (new RdfVocabularyProvider($fetcher))->getScheme($schemeId);

        self::assertNotNull($scheme);
        self::assertSame('One', $scheme->byId($conceptId)?->prefLabel->value('en'));
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

    public function testInMemoryCacheExpiresEntriesAtTheConfiguredTtl(): void {
        $clock = new class implements Clock {
            public int $timestamp = 100;

            public function now(): int {
                return $this->timestamp;
            }
        };
        $schemeId = 'http://example.test/scheme';
        $scheme = new VocabularyScheme($schemeId, new ConceptScheme($schemeId));
        $cache = new InMemoryVocabularyCache($clock);

        $cache->set($schemeId, $scheme, 10);
        self::assertSame($scheme, $cache->get($schemeId));

        $clock->timestamp = 110;

        self::assertNull($cache->get($schemeId));
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
