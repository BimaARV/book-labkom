<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/admin/lab-mappings/1/pc', 'POST', [], [], [], ['HTTP_CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode([
    'pc_id' => null,
    'grid_row' => 0,
    'grid_col' => 0,
    'name' => 'PC-1',
    'ip_address' => '',
    'mac_address' => '',
    'status' => 'active',
    'damage_description' => null
]));

$controller = new \App\Http\Controllers\Admin\LabMappingController();
$lab = \App\Models\Laboratory::first();

try {
    $response = $controller->savePc($request, $lab);
    echo "Response: " . $response->getContent() . "\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Error: \n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
