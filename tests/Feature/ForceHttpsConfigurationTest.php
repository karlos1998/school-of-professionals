<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ForceHttpsConfigurationTest extends TestCase
{
    public function test_it_forces_https_scheme_when_enabled(): void
    {
        config()->set('app.force_https', true);

        new AppServiceProvider($this->app)->boot();

        expect(URL::to('/admin'))->toStartWith('https://');
    }
}
