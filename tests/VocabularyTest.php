<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\InMemoryVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
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
}
