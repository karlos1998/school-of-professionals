<?php

use App\Services\ExamSync\ExamSyncQuestionScraper;

it('parses questions, strips prefixes, detects correct answers and image url', function (): void {
    $html = <<<'HTML'
    <section id="questions">
        <div id="1">
            <h3>1. Przebywanie osób jest:</h3>
            <div>
                <span>a) dozwolone</span>
                <h4>b) zabronione</h4>
                <span>c) dozwolone krótko</span>
            </div>
        </div>
        <div id="2">
            <img src="/_next/image?url=%2F_next%2Fstatic%2Fmedia%2Fabc.png&w=828&q=75" />
            <h3>2. Jaką odległość zachować?</h3>
            <div>
                <span>a) 1 m</span>
                <span>b) 2 m</span>
                <h4>c) 3 m</h4>
            </div>
        </div>
    </section>
    HTML;

    $scraper = new ExamSyncQuestionScraper;
    $questions = $scraper->parseQuestionsFromHtml($html, 'https://www.testy-wit.pl/foo/nauka');

    expect($questions)->toHaveCount(2);
    expect($questions[0]['content'])->toBe('Przebywanie osób jest:');
    expect($questions[0]['answers'][0]['content'])->toBe('dozwolone');
    expect($questions[0]['answers'][1]['is_correct'])->toBeTrue();
    expect($questions[1]['image_url'])->toBe('https://www.testy-wit.pl/_next/static/media/abc.png');
});
