<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IsyThl\EuropeanLearningModel\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\LearningOutcome;
use IsyThl\EuropeanLearningModel\LocalizedString;
use IsyThl\EuropeanLearningModel\Vocabulary\EscoVocabularySearchProvider;
use IsyThl\EuropeanLearningModel\Vocabulary\VocabularySearchResourceFetcher;

$fetcher = new class implements VocabularySearchResourceFetcher {
    public function search(
        string $schemeId,
        string $query,
        string $language,
        int $limit,
        ?string $cursor,
        array $fallbackLanguages = [],
    ): string {
        return json_encode([
            '_embedded' => [
                'results' => [[
                    'className' => 'Skill',
                    'uri' => 'http://data.europa.eu/esco/skill/example',
                    'preferredLabel' => ['en' => 'Read engineering drawings'],
                    'isInScheme' => [
                        $schemeId,
                        'http://data.europa.eu/esco/concept-scheme/member-skills',
                    ],
                ]],
            ],
        ], JSON_THROW_ON_ERROR);
    }
};

$provider = new EscoVocabularySearchProvider($fetcher);
$page = $provider->searchConcepts(
    ElmVocabularySchemes::ESCO_SKILLS,
    'engineering',
    'en',
    limit: 10,
);
$selectedSkill = $page->concepts[0] ?? null;
if ($selectedSkill === null) {
    throw new RuntimeException('The ESCO search returned no skills.');
}

$outcome = new LearningOutcome(
    'outcome-1',
    new LocalizedString(['en' => 'Interpret technical drawings']),
    relatedEscosSkills: [$selectedSkill],
);

echo $selectedSkill->prefLabel->value('en') . PHP_EOL;
echo $outcome->toJson() . PHP_EOL;
