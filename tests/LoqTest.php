<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanDigitalCredentials\Address;
use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\Identifier;
use IsyThl\EuropeanDigitalCredentials\LearningAchievementSpecification;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use IsyThl\EuropeanDigitalCredentials\Location;
use IsyThl\EuropeanDigitalCredentials\Note;
use IsyThl\EuropeanDigitalCredentials\Organisation;
use IsyThl\EuropeanDigitalCredentials\Qualification;
use IsyThl\EuropeanDigitalCredentials\WebResource;
use IsyThl\EuropeanLearningModel\Loq\LearningOpportunity;
use IsyThl\EuropeanLearningModel\Loq\LearningOpportunityDocument;
use IsyThl\EuropeanLearningModel\Loq\QualificationReference;
use IsyThl\EuropeanLearningModel\Loq\QualificationDocument;
use PHPUnit\Framework\TestCase;

final class LoqTest extends TestCase {

    public function testQualificationReferencePreservesPersistentIdentity(): void {
        $reference = new QualificationReference(
            new Identifier('qualification-id', '123', 'example-qualifications'),
            'https://example.test/datasets/qualifications',
        );

        self::assertSame([
            'type' => 'QualificationReference',
            'identifier' => [
                'id' => 'urn:epass:identifier:qualification-id',
                'type' => 'Identifier',
                'notation' => '123',
                'schemeName' => 'example-qualifications',
            ],
            'datasetNamespace' => 'https://example.test/datasets/qualifications',
        ], $reference->toArray());
    }

    public function testIncompleteQualificationReferenceFailsBeforeSerialization(): void {
        $this->expectException(InvalidCredentialException::class);

        new QualificationReference(null);
    }

    public function testDocumentRejectsNonLearningOpportunityRoots(): void {
        $this->expectException(InvalidCredentialException::class);

        new LearningOpportunityDocument([['type' => 'LearningOpportunity']]);
    }

    public function testQualificationDocumentRejectsMissingProfileFields(): void {
        $qualification = new Qualification(
            'qualification-1',
            new LocalizedString(['en' => 'Incomplete qualification']),
        );

        $this->expectException(InvalidCredentialException::class);

        new QualificationDocument([$qualification]);
    }

    public function testDocumentSerializationIsDeterministic(): void {
        $language = new Concept(
            'http://publications.europa.eu/resource/authority/language/ENG',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme(ElmVocabularySchemes::LANGUAGE),
            'ENG',
        );
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/BEL',
            new LocalizedString(['en' => 'Belgium']),
            new ConceptScheme(ElmVocabularySchemes::COUNTRY),
            'BEL',
        );
        $provider = new Organisation(
            'provider-1',
            new Location('location-1', new Address('address-1', $country, new Note('address-note', new LocalizedString([
                'en' => 'Brussels',
            ])))),
            new LocalizedString(['en' => 'Example Provider']),
        );
        $specification = new LearningAchievementSpecification(
            'https://example.test/specifications/1',
            new LocalizedString(['en' => 'Example qualification']),
        );
        $opportunity = new LearningOpportunity(
            'https://example.test/opportunity/123',
            new LocalizedString(['en' => 'Example opportunity']),
            $language,
            new WebResource('homepage-1', 'https://example.test/opportunity/123'),
            [$provider],
            $specification,
        );
        $document = new LearningOpportunityDocument([
            $opportunity,
        ]);

        self::assertSame($document->toJson(), $document->toJson());
        self::assertStringContainsString('"@graph"', $document->toJson());
    }
}
