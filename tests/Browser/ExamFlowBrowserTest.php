<?php

namespace Tests\Browser;

use Database\Seeders\ExamSeeder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExamFlowBrowserTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh');
        Artisan::call('db:seed', ['--class' => ExamSeeder::class]);
    }

    public function test_user_can_open_authority_list_and_see_branded_footer_link(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->waitForText('Witaj w platformie testowej')
                ->assertSee('UDT')
                ->assertSee('WIT')
                ->assertPresent('a.footer-link')
                ->assertAttribute('a.footer-link', 'href', 'https://letscode.it');
        });
    }

    public function test_user_can_choose_udt_class_and_reach_class_mode_selection_route(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/egzaminy/udt')
                ->waitForText('Katalog testow')
                ->click('div.test-card')
                ->waitForText('Wybierz klase: Dzwigi budowlane')
                ->clickLink('Klasa I')
                ->waitForLocation('/egzaminy/udt/dzwigi-budowlane/i')
                ->waitForText('Wybierz tryb testu')
                ->assertSee('Klasa I');
        });
    }

    public function test_user_can_start_exam_mode_and_see_exam_session_shell(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/egzaminy/wit/maszyny-drogowe')
                ->waitForText('Wybierz tryb testu')
                ->script('document.querySelector(\'a[href$="/tryb/exam"]\')?.click();');

            $browser
                ->waitForLocation('/egzaminy/wit/maszyny-drogowe/tryb/exam')
                ->waitForText('Pytanie 1 /')
                ->assertSee('Pytania:');
        });
    }

    public function test_user_can_expand_all_questions_in_study_mode(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/egzaminy/udt/dzwigi-budowlane/i')
                ->waitForText('Wybierz tryb testu')
                ->script('document.querySelector(\'a[href$="/tryb/study"]\')?.click();');

            $browser
                ->waitForLocation('/egzaminy/udt/dzwigi-budowlane/i/tryb/study')
                ->waitForText('Lista pytan')
                ->assertSee('Rozwin wszystko')
                ->assertDontSee('(Dzwigi budowlane) Pytanie 1. Jak postapic w obszarze: BHP i przygotowanie stanowiska?')
                ->click('[data-testid="study-toggle-all"]')
                ->waitForText('(Dzwigi budowlane) Pytanie 1. Jak postapic w obszarze: BHP i przygotowanie stanowiska?')
                ->assertSee('Zwin wszystko');
        });
    }
}
