<?php

namespace Tests\Browser;

use Database\Seeders\ExamSeeder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminPanelBrowserTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh');
        Artisan::call('db:seed', ['--class' => ExamSeeder::class]);
        Artisan::call('admin:sync');
    }

    public function test_admin_can_log_in_open_tests_and_classes_modules(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/admin-panel/login')
                ->waitForText('Logowanie do panelu admina')
                ->type('input[type="email"]', 'admin@example.com')
                ->type('input[type="password"]', 'asdasdasd')
                ->click('button[type="submit"]')
                ->waitForLocation('/admin-panel')
                ->assertPathIs('/admin-panel')
                ->assertSee('Testy')
                ->assertSee('Klasy')
                ->visit('/admin-panel/tests')
                ->waitForLocation('/admin-panel/tests')
                ->waitFor('@admin-tests-add-button')
                ->visit('/admin-panel/classes')
                ->waitForLocation('/admin-panel/classes')
                ->waitFor('@admin-classes-add-button');
        });
    }
}
