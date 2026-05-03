<?php

namespace Tests\Browser;

use App\Models\User;
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
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($admin): void {
            $browser->loginAs($admin)
                ->visit('/admin-panel')
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

    public function test_admin_can_log_out_from_panel(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($admin): void {
            $browser->loginAs($admin)
                ->visit('/admin-panel')
                ->waitForLocation('/admin-panel')
                ->logout()
                ->visit('/admin-panel/login')
                ->waitForLocation('/admin-panel/login')
                ->assertSee('Logowanie do panelu admina');
        });
    }
}
