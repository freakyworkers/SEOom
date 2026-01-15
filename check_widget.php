<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CustomPageWidget;
use App\Models\CustomPage;

$widget = CustomPageWidget::find(11);
if ($widget) {
    echo "Widget ID: " . $widget->id . "\n";
    echo "Custom Page ID: " . $widget->custom_page_id . "\n";
    echo "Container ID: " . $widget->container_id . "\n";
    echo "Type: " . $widget->type . "\n";
    
    $customPage = CustomPage::find($widget->custom_page_id);
    if ($customPage) {
        echo "\nCustom Page Site ID: " . $customPage->site_id . "\n";
    }
} else {
    echo "Widget not found\n";
}
