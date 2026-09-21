<?php
$routes = [
    'BATANGAS- ROXAS CAPIZ',
    'ROXAS CAPIZ - BATANGAS',
    'SIBUYAN (MAG)- ROXAS CAPIZ',
    'ROXAS CAPIZ - SIBUYAN (MAG)',
    'CAJIDIOCAN-ROXAS CAPIZ',
    'ROXAS CAPIZ-CAJIDIOCAN',
];

foreach ($routes as $r) {
    $clean = preg_replace('/\s*-\s*/', ' - ', $r);
    $parts = explode(' - ', $clean);
    echo "Raw: '{$r}' => Orig: '{$parts[0]}', Dest: '{$parts[1]}'" . PHP_EOL;
}
