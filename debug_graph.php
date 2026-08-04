<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$o = new App\Services\OutlookService();
$msgs = $o->getInboxMessages(10);
foreach($msgs as $m) {
    if(strpos(strtolower($m['subject']), 'test') !== false) {
        echo "Found message: " . $m['subject'] . "\n";
        $atts = $o->getMessageAttachments($m['id']);
        foreach($atts as &$a) {
            unset($a['contentBytes']);
        }
        echo json_encode($atts, JSON_PRETTY_PRINT) . "\n\n";
    }
}
