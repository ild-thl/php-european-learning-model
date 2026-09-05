<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class ElmVocabularySchemes {
    public const LANGUAGE = 'http://publications.europa.eu/resource/authority/language';
    public const COUNTRY = 'http://publications.europa.eu/resource/authority/country';
    public const CREDENTIAL = 'http://data.europa.eu/snb/credential/25831c2';
    public const EQF = 'http://data.europa.eu/snb/eqf/25831c2';
    public const QDR = 'http://data.europa.eu/snb/qdr/25831c2';
    public const QDR_BASE = 'http://data.europa.eu/snb/qdr/';
    public const LEARNING_SETTING = 'http://data.europa.eu/snb/learning-setting/25831c2';
    public const LEARNING_ACTIVITY = 'http://data.europa.eu/snb/learning-activity/25831c2';
    public const ASSESSMENT = 'http://data.europa.eu/snb/assessment/25831c2';
    public const ISCED_F = 'http://data.europa.eu/snb/isced-f/25831c2';
    public const ENTITLEMENT = 'http://data.europa.eu/snb/entitlement/25831c2';
    public const ESCO_SKILLS = 'http://data.europa.eu/esco/concept-scheme/skills';
    public const ACCREDITATION_DECISION = 'http://data.europa.eu/snb/accreditation-decision/25831c2';
    public const ACCREDITATION_STATUS = 'http://data.europa.eu/snb/status/25831c2';
    public const ATU = 'http://publications.europa.eu/resource/authority/atu';
    public const CONTENT_ENCODING = 'http://data.europa.eu/snb/encoding/25831c2';
    public const CONTENT_TYPE = 'http://publications.europa.eu/resource/authority/file-type';
    public const EDUCATION_CREDIT = 'http://data.europa.eu/snb/education-credit/25831c2';
    public const LEARNING_OPPORTUNITY = 'http://data.europa.eu/snb/learning-opportunity/25831c2';
    public const SKILL_REUSE_LEVEL = 'http://data.europa.eu/snb/skill-reuse-level/25831c2';
    public const SUPERVISION_VERIFICATION = 'http://data.europa.eu/snb/supervision-verification/25831c2';
    public const TARGET_GROUP = 'http://data.europa.eu/snb/target-group/25831c2';
    public const DCF_SKILLS = 'http://data.europa.eu/snb/dcf/25831c2';
    public const OCCUPATIONS = 'http://data.europa.eu/esco/concept-scheme/occupations';

    /** @return array<string, string> */
    public static function all(): array {
        return [
            'LANGUAGE' => self::LANGUAGE,
            'COUNTRY' => self::COUNTRY,
            'CREDENTIAL' => self::CREDENTIAL,
            'EQF' => self::EQF,
            'QDR' => self::QDR,
            'LEARNING_SETTING' => self::LEARNING_SETTING,
            'LEARNING_ACTIVITY' => self::LEARNING_ACTIVITY,
            'ASSESSMENT' => self::ASSESSMENT,
            'ISCED_F' => self::ISCED_F,
            'ENTITLEMENT' => self::ENTITLEMENT,
            'ESCO_SKILLS' => self::ESCO_SKILLS,
            'ACCREDITATION_DECISION' => self::ACCREDITATION_DECISION,
            'ACCREDITATION_STATUS' => self::ACCREDITATION_STATUS,
            'ATU' => self::ATU,
            'CONTENT_ENCODING' => self::CONTENT_ENCODING,
            'CONTENT_TYPE' => self::CONTENT_TYPE,
            'EDUCATION_CREDIT' => self::EDUCATION_CREDIT,
            'LEARNING_OPPORTUNITY' => self::LEARNING_OPPORTUNITY,
            'SKILL_REUSE_LEVEL' => self::SKILL_REUSE_LEVEL,
            'SUPERVISION_VERIFICATION' => self::SUPERVISION_VERIFICATION,
            'TARGET_GROUP' => self::TARGET_GROUP,
            'DCF_SKILLS' => self::DCF_SKILLS,
            'OCCUPATIONS' => self::OCCUPATIONS,
        ];
    }

    public static function requiresSearch(string $schemeId): bool {
        return in_array($schemeId, [
            self::LANGUAGE,
            self::COUNTRY,
            self::ATU,
            self::CONTENT_TYPE,
            self::QDR,
            self::ISCED_F,
            self::ESCO_SKILLS,
            self::DCF_SKILLS,
            self::OCCUPATIONS,
        ], true);
    }

    /** @return array<string, 'model-enforced'|'search-backed'> */
    public static function ownership(): array {
        return [
            'LANGUAGE' => 'search-backed',
            'COUNTRY' => 'search-backed',
            'CREDENTIAL' => 'model-enforced',
            'EQF' => 'model-enforced',
            'QDR' => 'search-backed',
            'LEARNING_SETTING' => 'model-enforced',
            'LEARNING_ACTIVITY' => 'model-enforced',
            'ASSESSMENT' => 'model-enforced',
            'ISCED_F' => 'search-backed',
            'ENTITLEMENT' => 'model-enforced',
            'ESCO_SKILLS' => 'search-backed',
            'ACCREDITATION_DECISION' => 'model-enforced',
            'ACCREDITATION_STATUS' => 'model-enforced',
            'ATU' => 'search-backed',
            'CONTENT_ENCODING' => 'model-enforced',
            'CONTENT_TYPE' => 'search-backed',
            'EDUCATION_CREDIT' => 'model-enforced',
            'LEARNING_OPPORTUNITY' => 'model-enforced',
            'SKILL_REUSE_LEVEL' => 'model-enforced',
            'SUPERVISION_VERIFICATION' => 'model-enforced',
            'TARGET_GROUP' => 'model-enforced',
            'DCF_SKILLS' => 'search-backed',
            'OCCUPATIONS' => 'search-backed',
        ];
    }
}
