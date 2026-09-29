<?php

// Render isolated fixtures: no external API calls or live campaign submissions.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['app.debug' => false, 'session.driver' => 'array', 'view.compiled' => storage_path('framework/views-test')]);
Illuminate\Support\Facades\Http::preventStrayRequests();
view()->share('errors', new Illuminate\Support\ViewErrorBag());
$controller = app(App\Http\Controllers\CampaignMenuController::class);
$pagesMethod = new ReflectionMethod($controller, 'pages');
$pages = $pagesMethod->invoke($controller);
$directory = storage_path('app/button-check');
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}
foreach (['campaign-lba' => ['sms', 'location-based-area'], 'campaign-targeted' => ['sms', 'targeted'], 'campaign-wa-lba' => ['wa-business', 'location-based-area'], 'campaign-wa-targeted' => ['wa-business', 'targeted'], 'campaign-wa-template' => ['wa-business', 'campaign-template']] as $view => [$channel, $menu]) {
    $data = ['channel' => $channel, 'menu' => $menu, 'page' => $pages[$channel][$menu], 'unitPrice' => 100, 'defaultCtaLink' => '', 'isMember' => false, 'waTemplates' => []];
    file_put_contents($directory.'/'.$view.'.html', view($view.'.create', $data)->render());
    echo $view." rendered\n";
}
