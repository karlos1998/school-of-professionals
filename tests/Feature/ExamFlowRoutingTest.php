<?php

namespace Tests\Feature;

use Database\Seeders\ExamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExamFlowRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_welcome_page_with_available_authorities(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('WelcomePage')
                ->has('authorities', 2)
                ->where('authorities.0.slug', 'udt')
                ->where('authorities.1.slug', 'wit'));
    }

    public function test_it_renders_mode_selection_for_wit_test_without_class(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/egzaminy/wit/maszyny-drogowe')
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('ModeSelectionPage')
                ->where('authority.slug', 'wit')
                ->where('test.slug', 'maszyny-drogowe')
                ->where('selectedClass', null)
                ->has('modeRoutes', 4));
    }

    public function test_it_requires_class_for_udt_test_that_has_only_class_variants(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/egzaminy/udt/dzwigi-budowlane')
            ->assertNotFound();
    }

    public function test_it_renders_mode_selection_for_udt_test_class_variant(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/egzaminy/udt/dzwigi-budowlane/i')
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('ModeSelectionPage')
                ->where('authority.slug', 'udt')
                ->where('test.slug', 'dzwigi-budowlane')
                ->where('selectedClass.slug', 'i')
                ->has('modeRoutes', 4));
    }

    public function test_it_renders_exam_session_for_selected_mode(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/egzaminy/udt/dzwigi-budowlane/i/tryb/exam')
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('ExamSessionPage')
                ->where('selectedMode', 'exam')
                ->where('exam.class.slug', 'i')
                ->where('examConfig.questionLimit', (int) config('exam.session.question_limit'))
                ->where('examConfig.passingThreshold', (int) config('exam.session.passing_threshold'))
                ->has('exam.questions', 30));
    }

    public function test_it_falls_back_to_sequential_mode_for_unknown_mode_slug(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/egzaminy/wit/maszyny-drogowe/tryb/nieznany')
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('ExamSessionPage')
                ->where('selectedMode', 'sequential'));
    }
}
