<?php

// Local browser fixtures only. Every upstream API request is intercepted.
require __DIR__.'/../vendor/autoload.php';
putenv('APP_ENV=testing');
putenv('SESSION_DRIVER=array');
putenv('CACHE_STORE=array');

$audit = new class('renderFixtures') extends Tests\TestCase {
    public function renderFixtures(): void
    {
        $this->setUp();
        try {
            config(['app.debug' => false, 'view.compiled' => storage_path('framework/views')]);
            Illuminate\Support\Facades\Http::preventStrayRequests();
            Illuminate\Support\Facades\Http::fake(fn () => Illuminate\Support\Facades\Http::response(['success' => true, 'data' => []]));
            $directory = storage_path('app/ui-audit');
            if (!is_dir($directory)) mkdir($directory, 0777, true);
            $pages = [
                'login' => '/', 'register' => '/register', 'forgot-password' => '/forgot-password',
                'reset-password' => '/reset-password/demo?email=demo@example.test',
            ];
            foreach ($pages as $name => $url) $this->saveFixture($directory, $name, $url);
            $this->withSession([
                'myads.access_token' => 'ui-audit-token', 'myads.gw_token' => 'ui-audit-gateway',
                'myads.user' => ['id' => 'demo', 'name' => 'Merchant ShopAds', 'email' => 'demo@example.test', 'role' => 'merchant'],
            ]);
            $this->saveFixture($directory, 'dashboard', '/dashboard');
            foreach (['sms', 'wa-business'] as $channel) {
                foreach (['location-based-area', 'targeted'] as $menu) {
                    foreach (['index', 'create'] as $view) {
                        $this->saveFixture($directory, "$channel-$menu-$view", "/campaign/$channel/$menu?view=$view");
                    }
                }
            }
            foreach (['index', 'create'] as $view) $this->saveFixture($directory, "wa-template-$view", "/campaign/wa-business/campaign-template?view=$view");
            foreach (['401', 'token-expired'] as $view) {
                file_put_contents("$directory/error-$view.html", view("errors.$view")->render());
            }
        } finally { $this->app->flush(); }
    }

    private function saveFixture(string $directory, string $name, string $url): void
    {
        $response = $this->get($url);
        $response->assertOk();
        file_put_contents("$directory/$name.html", $response->getContent());
        echo "$name rendered\n";
    }
};
$audit->renderFixtures();
