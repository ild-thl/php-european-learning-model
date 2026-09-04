<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\GradingScheme;
use IsyThl\EuropeanDigitalCredentials\InMemoryVocabularyProvider;
use IsyThl\EuropeanDigitalCredentials\LearningActivitySpecification;
use IsyThl\EuropeanDigitalCredentials\LearningAssessmentSpecification;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use IsyThl\EuropeanDigitalCredentials\VocabularyScheme;

$languageScheme = new ConceptScheme(ElmVocabularySchemes::LANGUAGE);
$languageEnglish = new Concept(
    'http://publications.europa.eu/resource/authority/language/ENG',
    new LocalizedString(['en' => 'English']),
    $languageScheme,
    'ENG',
);
$languageGerman = new Concept(
    'http://publications.europa.eu/resource/authority/language/DEU',
    new LocalizedString(['en' => 'German']),
    $languageScheme,
    'DEU',
);
$languageVocabulary = new VocabularyScheme(
    ElmVocabularySchemes::LANGUAGE,
    $languageScheme,
    [$languageEnglish, $languageGerman],
    new LocalizedString(['en' => 'Languages']),
    'example-snapshot',
);

$provider = new InMemoryVocabularyProvider([$languageVocabulary]);
$availableLanguages = $languageVocabulary->getConcepts();
$selectedLanguage = $provider->getConceptByNotation('ENG', ElmVocabularySchemes::LANGUAGE);
if ($selectedLanguage === null) {
    throw new RuntimeException('The selected language is not available.');
}

$activityScheme = new ConceptScheme(ElmVocabularySchemes::LEARNING_ACTIVITY);
$activityWorkshop = new Concept(
    'http://example.test/activity/workshop',
    new LocalizedString(['en' => 'Workshop', 'de' => 'Workshop']),
    $activityScheme,
    'workshop',
);
$activityLecture = new Concept(
    'http://example.test/activity/lecture',
    new LocalizedString(['en' => 'Lecture', 'de' => 'Vorlesung']),
    $activityScheme,
    'lecture',
);
$activityTypeScheme = new VocabularyScheme(
    ElmVocabularySchemes::LEARNING_ACTIVITY,
    $activityScheme,
    [$activityWorkshop, $activityLecture],
);
$availableActivityTypes = $activityTypeScheme->getConcepts();
$selectedActivityType = $activityTypeScheme->byNotation('workshop');
if ($selectedActivityType === null) {
    throw new RuntimeException('The selected activity type is not available.');
}

$activity = new LearningActivitySpecification(
    'activity-1',
    new LocalizedString(['en' => 'Introductory workshop']),
    type: $selectedActivityType,
    language: $selectedLanguage,
);

$assessmentScheme = new ConceptScheme(ElmVocabularySchemes::ASSESSMENT);
$selectedAssessment = new Concept(
    'http://example.test/assessment/exam',
    new LocalizedString(['en' => 'Exam']),
    $assessmentScheme,
    'exam',
);

$assessment = new LearningAssessmentSpecification(
    'assessment-1',
    new LocalizedString(['en' => 'Final assessment']),
    $selectedAssessment,
    new GradingScheme(
        'grading-1',
        new LocalizedString(['en' => 'Pass or fail']),
        new LocalizedString(['en' => 'Simple grading']),
    ),
    $selectedLanguage,
    $selectedAssessment,
);

echo $assessment->toJson() . PHP_EOL;
echo $activity->toJson() . PHP_EOL;
