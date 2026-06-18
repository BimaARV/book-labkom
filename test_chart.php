<?php

$childLabels = ['Unit A (100%, 5 Peminjaman)'];
$childData = [5];
$buPercentages = [100];
$buColors = ['#002B5C'];

$buChartConfig = [
    'type' => 'doughnut',
    'data' => [
        'labels' => $childLabels,
        'datasets' => [
            [
                'data' => $buPercentages,
                'backgroundColor' => $buColors,
            ]
        ]
    ],
    'options' => [
        'legend' => [ 'position' => 'bottom' ],
        'plugins' => [
            'datalabels' => [
                'color' => '#fff',
                'font' => ['weight' => 'bold', 'size' => 14],
                'formatter' => "function(value) { return value + '%'; }"
            ]
        ]
    ]
];
$url = 'https://quickchart.io/chart?w=600&h=450&c=' . urlencode(json_encode($buChartConfig));
echo $url . "\n";
try {
    $data = file_get_contents($url);
    if ($data === false) {
        echo "Failed to get contents\n";
    } else {
        echo "Success, length: " . strlen($data) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
