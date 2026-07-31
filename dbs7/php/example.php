<?php

require __DIR__ . '/CdmDataClient.php';

$accessKey = getenv('CDM_ACCESS_KEY') ?: 'AK...';
$secretKey = getenv('CDM_SECRET_KEY') ?: 'SK...';
$custId = getenv('CDM_CUST_ID') ?: '270000005';

$client = new CdmDataClient($accessKey, $secretKey);

// 1) ข้อมูลไฟฟ้ารายวันล่าสุด 1 record
$latest = $client->getDataDaily($custId, ['limit' => 1]);
print_r($latest);

// 2) ข้อมูลช่วงวันที่
$range = $client->getDataDaily($custId, [
    'from' => '2026-07-01',
    'to' => '2026-07-31',
    'limit' => 500,
    'order' => 'tb_datetime,asc',
]);
echo count($range['body']['records']) . " records\n";

// 3) อ่านสถานะ on/off ปัจจุบัน
$status = $client->getOnOffStatus($custId);
print_r($status);

// 4) สั่งเปิด/ปิดมิเตอร์ตัวใดตัวหนึ่ง (ต้องมี allowed_devices ครอบคลุม cust_id นี้)
// $result = $client->onOffCommand($custId, 'off', 'NS8888666796');
// print_r($result);
