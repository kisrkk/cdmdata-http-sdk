<?php

// ponytail: self-check for URL building only, no real network call
require __DIR__ . '/CdmDataClient.php';

$url = CdmDataClient::buildUrl('https://dbs7.cplservice.com', '/cdmdata/v1/getdatadaily', [
    'cust_id' => '270000005',
    'limit' => 1,
    'skip_me' => null,
]);

if (strpos($url, 'cust_id=270000005') === false) {
    fwrite(STDERR, "FAILED: cust_id missing from URL\n");
    exit(1);
}
if (strpos($url, 'limit=1') === false) {
    fwrite(STDERR, "FAILED: limit missing from URL\n");
    exit(1);
}
if (strpos($url, 'skip_me') !== false) {
    fwrite(STDERR, "FAILED: null param should be skipped\n");
    exit(1);
}

echo "CdmDataClient.php (dbs7 legacy) self-check OK\n";
