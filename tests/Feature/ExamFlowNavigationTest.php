<?php

namespace Tests\Feature;

use App\Models\ExamCategory;
use Database\Seeders\ExamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExamFlowNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_404_for_unknown_authority_slug(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/egzaminy/nieznany-organ')
            ->assertNotFound();
    }

    public function test_it_returns_404_for_unknown_test_slug_within_existing_authority(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/egzaminy/udt/nieistniejacy-test')
            ->assertNotFound();
    }

    public function test_it_renders_authority_tests_page_with_back_link_to_home(): void
    {
        $this->seed(ExamSeeder::class);

        $this->get('/egzaminy/wit')
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('AuthorityTestsPage')
                ->where('authority.slug', 'wit')
                ->where('homeUrl', route('exam-flow.welcome'))
                ->has('tests')
            );
    }

    public function test_it_lists_favorite_authority_tests_first(): void
    {
        $this->seed(ExamSeeder::class);

        ExamCategory::query()
            ->where('slug', 'wozki-jezdniowe')
            ->update(['is_favorite' => true]);

        $this->get('/egzaminy/udt')
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('AuthorityTestsPage')
                ->where('tests.0.slug', 'wozki-jezdniowe')
            );
    }
}
