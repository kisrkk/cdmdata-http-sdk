# PHP (legacy / dbs7)

ใช้ ext-curl ที่มากับ PHP มาตรฐานอยู่แล้ว — ไม่ต้องใช้ Composer/3rd-party lib ใช้กับ [legacy API](../../../(legacy)cdmdata-public-api.md)
(`cust_id`, ไฟฟ้า/น้ำ, `dbs7.cplservice.com`) — ถ้าต้องการ API รุ่นใหม่ (`device_id`) ดูที่ [`../../dbs5/php`](../../dbs5/php)

## Requirements

- PHP 7.4+ พร้อม extension `curl` และ `json` (เปิดอยู่โดย default ในเกือบทุก distro)

## Run

```bash
export CDM_ACCESS_KEY="AK..."
export CDM_SECRET_KEY="SK..."
export CDM_CUST_ID="270000005"   # optional, มี default อยู่แล้ว

php example.php
```

## Self-check (ไม่ต้องมี API key)

```bash
php selfcheck.php
```

ตรวจแค่ logic การสร้าง URL ไม่ยิง network จริง
