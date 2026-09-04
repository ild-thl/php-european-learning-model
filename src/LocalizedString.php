<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class LocalizedString {

    /** @var array<string, list<string>> */
    private array $translations;

    /**
     * @param array<string, string|list<string>> $translations
     */
    public function __construct(array $translations) {
        if ($translations === []) {
            throw new InvalidCredentialException('At least one localized value is required.');
        }

        $this->translations = [];
        foreach ($translations as $language => $values) {
            $isValidLanguage = preg_match(
                '/^[a-z]{2,3}(?:-[A-Z][a-z]{3})?(?:-[A-Z]{2}|-[0-9]{3})?$/',
                $language,
            ) === 1;
            if (!$isValidLanguage) {
                throw new InvalidCredentialException('Language tags must use the BCP 47 language format.');
            }

            $values = is_string($values) ? [$values] : $values;
            if (
                $values === []
                || array_filter($values, static fn ($value): bool => !is_string($value) || $value === '') !== []
            ) {
                throw new InvalidCredentialException('Localized values must be non-empty strings.');
            }

            $this->translations[$language] = array_values($values);
        }
    }

    /** @return array<string, list<string>> */
    public function toArray(): array {
        return $this->translations;
    }

    /**
     * @param list<string> $fallbackLanguages
     */
    public function value(string $language, array $fallbackLanguages = []): ?string {
        foreach (array_merge([$language], $fallbackLanguages) as $candidate) {
            if (isset($this->translations[$candidate])) {
                return $this->translations[$candidate][0];
            }
        }

        return $this->translations[array_key_first($this->translations)][0] ?? null;
    }
}
