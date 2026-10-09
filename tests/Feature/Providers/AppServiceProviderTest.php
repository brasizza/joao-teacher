<?php

namespace Tests\Feature\Providers;

use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_generated_urls_use_https_when_forcing_https_is_enabled(): void
    {
        config(['app.force_https' => true]);
        URL::setRequest(Request::create('http://localhost'));
        URL::forceRootUrl(null);
        URL::forceScheme(null);

        (new AppServiceProvider(app()))->boot();

        $this->assertSame('https://localhost/example', url('/example'));
    }

    public function test_generated_urls_keep_http_when_forcing_https_is_disabled(): void
    {
        config(['app.force_https' => false]);
        URL::setRequest(Request::create('http://localhost'));
        URL::forceRootUrl(null);
        URL::forceScheme(null);

        (new AppServiceProvider(app()))->boot();

        $this->assertSame('http://localhost/example', url('/example'));
    }
}
