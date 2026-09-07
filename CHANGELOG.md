# Changelog

All notable package changes are recorded here.

## [0.1.0] - 2026-09-07

- Added the framework-independent `Core`, `Edc`, and `Loq` package layers under
  the `isy-thl/european-learning-model` package identity.
- Added deterministic unsigned JSON-LD models for EDC credentials and LOQ
  qualification and learning-opportunity documents.
- Added strict EDC document validation for identifiers, language maps, nested
  references, UTC date ordering, typed claims, and generic-full/generic-no-CV
  profile selection.
- Added LOQ validation for persistent identifiers, publisher metadata, default
  language, learning outcomes, EQF, NQF, ISCED-F, and external qualification
  references.
- Added shared typed values and entities for concepts, localization,
  identifiers, dates, notes, credit points, accreditations, awarding
  opportunities, and supplementary web resources.
- Added deterministic vocabulary providers, bounded search adapters, cache
  expiry control, and controlled-scheme ownership coverage.
- Added bundled ELM, EDC, and LOQ profile evidence with local resource
  resolution and no hidden network retrieval.
- Preserved PHP 8.1 compatibility and plain-PHP operation without Moodle,
  QDR, DSS, CSC, signing, or transport dependencies.

## Deferred

- Verified LOQ XML serialization and QDR export remain intentionally deferred.
- Real RDF/JSON-LD/SHACL execution remains an injectable application boundary;
  PHP preflight validation does not claim full SHACL conformance.
- Moodle, QDR delivery, and DSS/CSC signing integrations remain outside this
  package and are planned as separate application or adapter work.
