# European Digital Credentials

Framework-independent ELM/EDC document models and deterministic unsigned JSON-LD serialization.

This package owns the ELM format boundary only. It does not contain Moodle,
Laravel, DSS, CSC, HTTP, certificate, or signing code. It produces unsigned
JSON-LD bytes that an application may pass unchanged to a signing boundary.

## Status

The package currently includes credential, issuer, subject, display,
achievement, awarding, qualification, accreditation, concept, localization,
identifier, date, note, credit-point, supplementary web-resource, and
vocabulary-snapshot models. `InMemoryVocabularyProvider` can enumerate allowed
concepts and look them up by scheme and identifier without network access.
Profile coverage is being extracted incrementally from the Moodle integration;
unsupported profile entities are not silently accepted.

## Usage

```php
use IsyThl\EuropeanDigitalCredentials\Credential;

$credential = new Credential(
	'credential-1',
	$subject,
	$displayParameter,
	new DateTimeImmutable('2026-01-01T00:00:00+00:00'),
);

$unsignedJsonLd = $credential->toJson();
```

Serialization is deterministic for equivalent object state. Dates are
normalized to UTC and emitted as `Y-m-d\\TH:i:s\\Z`; unset optional fields are
omitted. Constructors validate typed child entities and controlled concepts.
Vocabulary retrieval is an explicit provider boundary: the package does not
perform hidden network access, and an application can add an HTTP provider and
cache decorator for authoritative vocabulary browsing. The package never
handles signatures, keys, certificates, or transport configuration.

## Development

```sh
composer install
composer test
```