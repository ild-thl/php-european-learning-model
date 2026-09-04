<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class ElmVocabularySchemes {
    public const string LANGUAGE = 'http://publications.europa.eu/resource/authority/language';
    public const string COUNTRY = 'http://publications.europa.eu/resource/authority/country';
    public const string CREDENTIAL = 'http://data.europa.eu/snb/credential/25831c2';
    public const string EQF = 'http://data.europa.eu/snb/eqf/25831c2';
    public const string QDR = 'http://data.europa.eu/snb/qdr/25831c2';
    public const string QDR_BASE = 'http://data.europa.eu/snb/qdr/';
    public const string LEARNING_SETTING = 'http://data.europa.eu/snb/learning-setting/25831c2';
    public const string LEARNING_ACTIVITY = 'http://data.europa.eu/snb/learning-activity/25831c2';
    public const string ASSESSMENT = 'http://data.europa.eu/snb/assessment/25831c2';
    public const string ISCED_F = 'http://data.europa.eu/snb/isced-f/25831c2';
    public const string ACCREDITATION = 'http://data.europa.eu/snb/accreditation/25831c2';
    public const string ENTITLEMENT = 'http://data.europa.eu/snb/entitlement/25831c2';
    public const string ESCO_SKILLS = 'http://data.europa.eu/esco/concept-scheme/skills';
    public const string ACCREDITATION_DECISION = 'http://data.europa.eu/snb/accreditation-decision/25831c2';
    public const string ACCREDITATION_STATUS = 'http://data.europa.eu/snb/status/25831c2';
    public const string ATU = 'http://publications.europa.eu/resource/authority/atu';
    public const string CONTENT_ENCODING = 'http://data.europa.eu/snb/encoding/25831c2';
    public const string CONTENT_TYPE = 'http://publications.europa.eu/resource/authority/file-type';
    public const string EDUCATION_CREDIT = 'http://data.europa.eu/snb/education-credit/25831c2';
    public const string LEARNING_OPPORTUNITY = 'http://data.europa.eu/snb/learning-opportunity/25831c2';
    public const string SKILL_REUSE_LEVEL = 'http://data.europa.eu/snb/skill-reuse-level/25831c2';
    public const string SUPERVISION_VERIFICATION = 'http://data.europa.eu/snb/supervision-verification/25831c2';
    public const string TARGET_GROUP = 'http://data.europa.eu/snb/target-group/25831c2';
    public const string DCF_SKILLS = 'http://data.europa.eu/snb/dcf/25831c2';
    public const string OCCUPATIONS = 'http://data.europa.eu/esco/concept-scheme/occupations';

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
            'ACCREDITATION' => self::ACCREDITATION,
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
}
