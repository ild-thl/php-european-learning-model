# European Digital Credentials

Framework-independent ELM/EDC document models and deterministic unsigned JSON-LD serialization.

This package owns the ELM format boundary only. It does not contain Moodle,
Laravel, DSS, CSC, HTTP, certificate, or signing code.

## Status

Initial package skeleton. The model and profile-specific entities are being
extracted incrementally from the Moodle integration.

## Development

```sh
composer install
composer test
```