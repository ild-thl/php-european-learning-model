# European Learning Model

`isy-thl/european-learning-model` is a framework-independent PHP package for
building European Learning Model (ELM) documents and deterministic unsigned
JSON-LD. It currently focuses on European Digital Credentials (EDC), with
shared ELM entities and incremental Learning Opportunities and Qualifications
(LOQ) support.

The package produces document bytes. An application owns persistence,
QDR delivery, HTTP, logging, and signing.

## Current scope

The package currently provides:

- shared ELM values and graph entities under `Core`;
- EDC credentials, subjects, claims, displays, issuers, and profile validation
	under `Edc`;
- LOQ qualifications, learning opportunities, references, document roots, and
	profile preflight validation under `Loq`;
- controlled concepts, localized values, identifiers, dates, and vocabulary
	provider contracts; and
- deterministic JSON-LD serialization with typed validation failures.

The package does not provide:

- Framework specific adapters or database integration.
- QDR HTTP clients, upload, hosting, API keys, or publication workflows;
- DSS, CSC, JAdES, certificates, private keys, signatures, or proofs; or
- a verified LOQ XML serializer.

EDC output is unsigned JSON-LD suitable for an application to pass unchanged
to a separate signing boundary such as `isy-thl/dss-csc-signing`.

## Requirements

- PHP 8.1 or newer
- PHP `mbstring` extension
- Composer

Development and vocabulary-provider tests may also require PHP DOM/XML
extensions.

## Installation

```sh
composer require isy-thl/european-learning-model
```

The package namespace is `IsyThl\EuropeanLearningModel`.

## Build an EDC credential

The main output is deterministic unsigned JSON-LD. Required child objects are
constructed explicitly, and `Credential::toJson()` performs document
validation before returning bytes.

```php
use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Edc\Claim;
use IsyThl\EuropeanLearningModel\Edc\Credential;
use IsyThl\EuropeanLearningModel\Edc\CredentialSubject;
use IsyThl\EuropeanLearningModel\Edc\DisplayParameter;

$english = new Concept(
	'http://publications.europa.eu/resource/authority/language/ENG',
	new LocalizedString(['en' => 'English']),
	new ConceptScheme(ElmVocabularySchemes::LANGUAGE),
	'ENG',
);

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

$credential = new Credential(
	'credential-1',
	$subject,
	new DisplayParameter(
		'display-1',
		$english,
		$english,
		new LocalizedString(['en' => 'European Digital Credential']),
	),
	new DateTimeImmutable('2026-01-01T00:00:00+00:00'),
);

$unsignedJsonLd = $credential->toJson();
```

Equivalent object state produces byte-for-byte identical output. Dates are
normalized to UTC and emitted as `Y-m-d\TH:i:s\Z`; unset optional fields are
omitted. JSON encoding uses explicit flags and `JSON_THROW_ON_ERROR`.

## Profiles and validation

EDC and LOQ validation are separate profile concerns:

- `EdcProfile::GENERIC_NO_CV` and `EdcProfile::GENERIC_FULL` identify the
	supported EDC profiles and their bundled RDF resources.
- `EdcDocumentValidator` checks the EDC document root and selected profile.
- `LoqProfileValidator` checks LOQ-specific identifiers, publisher metadata,
	default language, learning outcomes, EQF, NQF, and ISCED-F requirements.
- `LoqDocumentValidator` validates LOQ document aggregates before
	serialization.

The PHP validators are preflight checks. Standards-based RDF/JSON-LD/SHACL
execution remains an injectable boundary; the package does not claim full
SHACL conformance from the PHP checks alone.

Profile resources are bundled under `resources/profile/` and are loaded by
explicit local resource boundaries. Constructors, validators, and serializers
do not retrieve profiles or vocabularies from the network.

## LOQ documents

TODO

## Controlled vocabularies

For small, trusted vocabularies, use `InMemoryVocabularyProvider` and a
`VocabularyScheme`. For injected RDF or JSON resources, use
`RdfVocabularyProvider` or `JsonLdVocabularyProvider` with an application-owned
`VocabularyResourceFetcher`. For large schemes, use the bounded search
providers under `Vocabulary`, including the ESCO search provider.

Vocabulary access is explicit and injectable. The package does not own HTTP
credentials, endpoints, caching policy, or live retrieval from constructors.
See the working examples:

- `examples/vocabulary-selection.php`
- `examples/vocabulary-providers.php`
- `examples/esco-search.php`

## Development and testing

```sh
composer install
composer test
composer analyse
composer style
composer lint
composer validate --strict
git diff --check
```

The test suite is plain PHP and does not require Moodle, QDR, DSS, CSC, or
network services. Optional live vocabulary qualification requires network
access and can be run separately:

```sh
ELM_LIVE_VOCABULARY_TESTS=1 vendor/bin/phpunit tests/LiveVocabularyTest.php
```

## License

GPL-3.0-or-later
