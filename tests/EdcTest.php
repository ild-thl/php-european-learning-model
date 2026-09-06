<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Claim;
use IsyThl\EuropeanLearningModel\Concept;
use IsyThl\EuropeanLearningModel\ConceptScheme;
use IsyThl\EuropeanLearningModel\Credential;
use IsyThl\EuropeanLearningModel\CredentialSubject;
use IsyThl\EuropeanLearningModel\DisplayParameter;
use IsyThl\EuropeanLearningModel\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Edc\EdcDocumentValidator;
use IsyThl\EuropeanLearningModel\Edc\EdcProfile;
use PHPUnit\Framework\TestCase;

final class EdcTest extends TestCase {

    public function testProfilesExposePinnedSchemaAndResourceIdentity(): void {
        self::assertSame(
            'http://data.europa.eu/snb/model/ap/edc-generic-no-cv',
            EdcProfile::GENERIC_NO_CV->value,
        );
        self::assertSame('EDC-generic-no-cv.rdf', EdcProfile::GENERIC_NO_CV->resourceName());
        self::assertSame('EDC-generic-full.rdf', EdcProfile::GENERIC_FULL->resourceName());
    }

    public function testNoCvValidatorAcceptsAValidNoCvDocument(): void {
        $document = $this->fixture();
        $document['credentialSchema']['id'] = EdcProfile::GENERIC_NO_CV->value;

        $validator = new EdcDocumentValidator(EdcProfile::GENERIC_NO_CV);
        $validator->validate($document);

        self::assertSame(EdcProfile::GENERIC_NO_CV, $validator->profile());
    }

    public function testProfileValidatorRejectsDocumentWithWrongSchema(): void {
        $this->expectExceptionMessage('Credential schema has an unsupported identifier.');

        (new EdcDocumentValidator(EdcProfile::GENERIC_NO_CV))->validate($this->fixture());
    }

    /** @return array<string, mixed> */
    private function fixture(): array {
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ada']),
            new LocalizedString(['en' => 'Lovelace']),
            new LocalizedString(['en' => 'Ada Lovelace']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array {
                    return ['id' => $this->id, 'type' => 'Claim'];
                }
            }],
        );
        $language = new Concept(
            'http://publications.europa.eu/resource/authority/language/ENG',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme(ElmVocabularySchemes::LANGUAGE),
            'ENG',
        );

        return (new Credential(
            'credential-1',
            $subject,
            new DisplayParameter('display-1', $language, $language, new LocalizedString(['en' => 'Title'])),
            new DateTimeImmutable('2024-01-01T00:00:00+00:00'),
        ))->toArray();
    }
}
