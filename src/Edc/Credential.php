<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\VocabularyScheme;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Core\Organisation;
use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Core\DateTimeFormatter;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Credential extends \IsyThl\EuropeanLearningModel\Core\Entity {

    private const CONTEXT = [
        'https://www.w3.org/2018/credentials/v1',
        'http://data.europa.eu/snb/model/context/edc-ap',
    ];

    public function __construct(
        string $id,
        public readonly CredentialSubject $credentialSubject,
        public readonly DisplayParameter $displayParameter,
        public readonly DateTimeImmutable $validFrom,
        public readonly ?DateTimeImmutable $expirationDate = null,
        ?Concept $credentialProfile = null,
        public readonly ?DateTimeImmutable $issuanceDate = null,
        public readonly ?DateTimeImmutable $issued = null,
        public readonly ?DateTimeImmutable $validUntil = null,
        public readonly ?Organisation $issuer = null,
    ) {
        parent::__construct($id);
        if ($issuer !== null && $issuer->eidasLegalIdentifier === null) {
            throw new InvalidCredentialException(
                'A credential issuer requires an eIDAS legal identifier.',
            );
        }
        $this->credentialProfile = $credentialProfile ?? new Concept(
            'http://data.europa.eu/snb/credential/e34929035b',
            new LocalizedString(['en' => 'Generic']),
            new ConceptScheme(ElmVocabularySchemes::CREDENTIAL),
        );
        if ($this->credentialProfile->inScheme->id !== ElmVocabularySchemes::CREDENTIAL) {
            throw new InvalidCredentialException(
                'Credential profile must belong to the ELM credential profile scheme.',
            );
        }
        $dates = [
            'expirationDate' => $expirationDate,
            'issuanceDate' => $issuanceDate,
            'issued' => $issued,
            'validUntil' => $validUntil,
        ];
        foreach ($dates as $field => $date) {
            if ($date !== null && $date < $validFrom) {
                throw new InvalidCredentialException(sprintf('Credential %s must not precede validFrom.', $field));
            }
        }
    }

    public readonly Concept $credentialProfile;

    public function validateProfile(VocabularyScheme $scheme): void {
        $scheme->assertContains($this->credentialProfile, 'credentialProfile');
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:credential:' . $this->id,
            'type' => ['VerifiableCredential', 'EuropeanDigitalCredential'],
            'credentialProfiles' => [$this->credentialProfile->toArray()],
            'displayParameter' => $this->displayParameter->toArray(),
            'credentialSchema' => [
                'id' => 'http://data.europa.eu/snb/model/ap/edc-generic-full',
                'type' => 'ShaclValidator2017',
            ],
            'credentialSubject' => $this->credentialSubject->toArray(),
            'validFrom' => DateTimeFormatter::format($this->validFrom),
            '@context' => self::CONTEXT,
        ];
        if ($this->expirationDate !== null) {
            $data['expirationDate'] = DateTimeFormatter::format($this->expirationDate);
        }
        if ($this->issuer !== null) {
            $data['issuer'] = $this->issuer->toArray();
        }
        if ($this->issuanceDate !== null) {
            $data['issuanceDate'] = DateTimeFormatter::format($this->issuanceDate);
        }
        if ($this->issued !== null) {
            $data['issued'] = DateTimeFormatter::format($this->issued);
        }
        if ($this->validUntil !== null) {
            $data['validUntil'] = DateTimeFormatter::format($this->validUntil);
        }
        return $data;
    }

    public function toJson(): string {
        $json = parent::toJson();
        (new CredentialDocumentValidator())->validateJson($json);

        return $json;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            Concept::fromArray($data['credentialProfile']),
            DisplayParameter::fromArray($data['displayParameter']),
            CredentialSubject::fromArray($data['credentialSubject']),
            isset($data['issuer']) ? Organisation::fromArray($data['issuer']) : null,
            isset($data['expirationDate']) ? new DateTimeImmutable($data['expirationDate']) : null,
            isset($data['issuanceDate']) ? new DateTimeImmutable($data['issuanceDate']) : null,
            isset($data['issued']) ? new DateTimeImmutable($data['issued']) : null,
            isset($data['validFrom']) ? new DateTimeImmutable($data['validFrom']) : null,
            isset($data['validUntil']) ? new DateTimeImmutable($data['validUntil']) : null,
        );
    }
}
