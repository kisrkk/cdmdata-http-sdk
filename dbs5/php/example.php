<?php

require __DIR__ . '/CdmDataClient.php';

$accessKey = getenv('CDM_ACCESS_KEY') ?: 'AK...';
$secretKey = getenv('CDM_SECRET_KEY') ?: 'SK...';
$deviceId = getenv('CDM_DEVICE_ID') ?: '0004236908';

$client = new CdmDataClient($accessKey, $secretKey);

// 1) ข้อมูล hourly ล่าสุด 1 record
$latest = $client->getDataHourly($deviceId, ['limit' => 1, 'transform' => 1]);
print_r($latest);

// 2) ข้อมูลช่วงวันที่
$range = $client->getDataHourly($deviceId, [
    'from' => '2025-06-01',
    'to' => '2025-06-30',
    'limit' => 500,
    'order' => 'datetime,asc',
    'transform' => 1,
]);
echo count($range['body']['records']) . " records\n";

// 3) สั่งเปิด/ปิด (ต้องมี allowed_devices ครอบคลุม device_id นี้)
// $result = $client->onOffCommand($deviceId, '01', 'on');
// print_r($result);
