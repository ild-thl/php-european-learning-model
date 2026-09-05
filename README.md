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
concepts and look them up by scheme, identifier, or notation without network
access. `ElmVocabularySchemes` provides stable identifiers for the profile-owned
schemes used by the current credential graph.
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

### Browse and select concepts

For deterministic application code, ship or construct a trusted
`VocabularyScheme` and use `InMemoryVocabularyProvider`:

```php
$language = $provider->getConceptByNotation('ENG', ElmVocabularySchemes::LANGUAGE);
$assessmentType = $provider->getConcept(
	'https://example.test/assessment/exam',
	ElmVocabularySchemes::ASSESSMENT,
);
$assessment = new LearningAssessmentSpecification(
	'assessment-1',
	new LocalizedString(['en' => 'Final assessment']),
	$assessmentType,
	$gradingScheme,
	$language,
	$assessmentType,
);
```

Use `JsonLdVocabularyProvider` or `RdfVocabularyProvider` when the resource
must be retrieved through an application-owned HTTP adapter. Both accept the
same `VocabularyResourceFetcher`; wrap either provider with
`CachedVocabularyProvider` to avoid repeated source retrievals:

```php
$provider = new CachedVocabularyProvider(
	new RdfVocabularyProvider($fetcher),
	new InMemoryVocabularyCache(),
);
$availableLanguages = $provider->getScheme(ElmVocabularySchemes::LANGUAGE);
$selectedLanguage = $provider->getConceptByNotation('ENG', ElmVocabularySchemes::LANGUAGE);
```

The complete offline selection and entity-construction example is in
`examples/vocabulary-selection.php`. It selects an allowed language by
notation and uses it, together with a selected assessment concept, to build a
validated `LearningAssessmentSpecification` and deterministic JSON-LD.
`examples/vocabulary-providers.php` shows the same injected fetcher boundary
with JSON-LD and RDF/XML resources, cache wrapping, and lookup by notation or
URI without making a live network request.
`examples/esco-search.php` demonstrates the ESCO HAL search adapter, selecting
one returned skill, and using it in a validated `LearningOutcome`.

For a trainer form backed by a finite scheme, use the scheme itself as the
dropdown source and keep only the selected concept in the model:

```php
$availableActivityTypes = $activityTypeScheme->getConcepts();
foreach ($availableActivityTypes as $concept) {
	$label = $concept->prefLabel->value($userLanguage, ['en']);
	// Render $concept->id as the option value and $label as its text.
}
$selectedType = $activityTypeScheme->byId($submittedConceptId);
// Or: $selectedType = $activityTypeScheme->byNotation($submittedNotation);
if ($selectedType === null) {
	throw new InvalidArgumentException('Unknown learning activity type.');
}
$activity = new LearningActivitySpecification(
	'activity-1',
	new LocalizedString(['en' => 'Introductory workshop']),
	type: $selectedType,
	language: $selectedLanguage,
);
```

For large schemes, do not call `getScheme()` to build the dropdown. Inject an
HTTP-backed `Vocabulary\VocabularySearchResourceFetcher` into
`Vocabulary\JsonVocabularySearchProvider`
and request a bounded page:

```php
$page = $searchProvider->searchConcepts(
	ElmVocabularySchemes::ESCO_SKILLS,
	$searchText,
	$userLanguage,
	limit: 50,
	cursor: $cursor,
	fallbackLanguages: ['en'],
);
foreach ($page->concepts as $concept) {
	// Render $concept->id, $concept->notation, and its preferred label.
}
$nextCursor = $page->nextCursor;
```

For ESCO skills and occupations, use `Vocabulary\EscoVocabularySearchProvider`
with the same injected fetcher contract. It parses ESCO HAL search responses,
validates the requested scheme and resource type, normalizes external language
tags such as `en-us`, and preserves the API's next link as the opaque cursor.

Serialization is deterministic for equivalent object state. Dates are
normalized to UTC and emitted as `Y-m-d\\TH:i:s\\Z`; unset optional fields are
omitted. Constructors validate typed child entities and controlled concepts.
Vocabulary retrieval is an explicit provider boundary: JSON-LD and RDF/XML
providers accept an injected resource fetcher and can be wrapped by a cache
decorator for authoritative vocabulary browsing. The package never handles
signatures, keys, certificates, or transport configuration. Field-specific
membership policies are enforced for credential profiles, languages, countries,
education credits, EQF/NQF, assessments, verification, entitlements, media,
and ISCED-F subjects. Complete SHACL validation is still being extracted.

The supplied `AA-Annex1-MC-unsigned.json` is retained as profile evidence. Its
legacy `credential` wrapper, schema array, missing JSON-LD context, and offset
date are intentional fixture differences; generated package documents use the
strict top-level ELM shape, context, schema object, and UTC date representation.

The legacy concept classes are represented as ordinary `Concept` values with
field-owned scheme assertions rather than empty package subclasses. This
preserves the ELM JSON-LD shape while rejecting a concept from the wrong
controlled list at construction time. NQF concepts are validated against the
dynamic `http://data.europa.eu/snb/qdr/` scheme family.

The package is not yet concept-complete. Remaining legacy controlled fields
include application-specific qualification-code schemes. Achievement
specification, learning-outcome, entitlement-occupation, and accreditation
controlled fields currently present in the package enforce their corresponding
scheme rules at construction time. Qualification codes are therefore accepted
as typed concepts with an explicit scheme supplied by the application; the
package preserves that scheme and does not claim a universal qualification
framework URI.

Vocabulary responsibilities are deliberately split:

- `Concept`, `ConceptScheme`, and `LocalizedString` are immutable ELM values.
- `VocabularyScheme` is a finite, trusted snapshot used for dropdowns and
	exact selection by ID or notation.
- `VocabularyProvider` loads complete snapshots; the JSON-LD and RDF/XML
	implementations are appropriate when the selected scheme is small enough to
	enumerate.
- The `Vocabulary` namespace contains `VocabularySearchProvider`,
	`VocabularyConceptPage`, `VocabularySearchResourceFetcher`, and
	`JsonVocabularySearchProvider`. Together they provide bounded,
	cursor-based search for large online schemes. The package parses and
	validates results but leaves HTTP, authentication, endpoint URLs, and
	transport policy to the adapter.
- `VocabularyCache` and `CachedVocabularyProvider` cache finite snapshots or
	delegate paged search without silently loading the full remote vocabulary.

## Development

```sh
composer install
composer test
# Optional live endpoint qualification; requires network access and ext-curl.
ELM_LIVE_VOCABULARY_TESTS=1 vendor/bin/phpunit tests/LiveVocabularyTest.php
```

The live qualification intentionally distinguishes retrieval modes. Language,
country, ATU, file type, QDR, ISCED-F, DCF skills, and occupations are not
loaded as complete snapshots. They require bounded search or an
application-owned endpoint adapter. ESCO skills and occupations are qualified
through the documented ESCO search API using the package's HAL parser.