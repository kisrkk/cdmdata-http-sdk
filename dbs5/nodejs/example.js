'use strict';

const { CdmDataClient } = require('./cdmdata-client');

const ACCESS_KEY = process.env.CDM_ACCESS_KEY || 'AK...';
const SECRET_KEY = process.env.CDM_SECRET_KEY || 'SK...';
const DEVICE_ID = process.env.CDM_DEVICE_ID || '0004236908';

const client = new CdmDataClient(ACCESS_KEY, SECRET_KEY);

(async () => {
  // 1) ข้อมูล hourly ล่าสุด 1 record
  const latest = await client.getDataHourly(DEVICE_ID, { limit: 1, transform: 1 });
  console.log(latest);

  // 2) ข้อมูลช่วงวันที่
  const range = await client.getDataHourly(DEVICE_ID, {
    from: '2025-06-01',
    to: '2025-06-30',
    limit: 500,
    order: 'datetime,asc',
    transform: 1,
  });
  console.log(range.body.records.length, 'records');

  // 3) สั่งเปิด/ปิด (ต้องมี allowed_devices ครอบคลุม device_id นี้)
  // const result = await client.onOffCommand(DEVICE_ID, '01', 'on');
  // console.log(result);
})().catch((err) => {
  console.error(err);
  process.exit(1);
});
