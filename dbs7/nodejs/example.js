'use strict';

const { CdmDataClient } = require('./cdmdata-client');

const ACCESS_KEY = process.env.CDM_ACCESS_KEY || 'AK...';
const SECRET_KEY = process.env.CDM_SECRET_KEY || 'SK...';
const CUST_ID = process.env.CDM_CUST_ID || '270000005';

const client = new CdmDataClient(ACCESS_KEY, SECRET_KEY);

(async () => {
  // 1) ข้อมูลไฟฟ้ารายวันล่าสุด 1 record
  const latest = await client.getDataDaily(CUST_ID, { limit: 1 });
  console.log(latest);

  // 2) ข้อมูลช่วงวันที่
  const range = await client.getDataDaily(CUST_ID, {
    from: '2026-07-01',
    to: '2026-07-31',
    limit: 500,
    order: 'tb_datetime,asc',
  });
  console.log(range.body.records.length, 'records');

  // 3) อ่านสถานะ on/off ปัจจุบัน
  const status = await client.getOnOffStatus(CUST_ID);
  console.log(status);

  // 4) สั่งเปิด/ปิดมิเตอร์ตัวใดตัวหนึ่ง (ต้องมี allowed_devices ครอบคลุม cust_id นี้)
  // const result = await client.onOffCommand(CUST_ID, 'off', { meterAddress: 'NS8888666796' });
  // console.log(result);
})().catch((err) => {
  console.error(err);
  process.exit(1);
});
