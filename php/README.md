# PHP

ใช้ ext-curl ที่มากับ PHP มาตรฐานอยู่แล้ว — ไม่ต้องใช้ Composer/3rd-party lib

## Requirements

- PHP 7.4+ พร้อม extension `curl` และ `json` (เปิดอยู่โดย default ในเกือบทุก distro)

## Run

```bash
export CDM_ACCESS_KEY="AK..."
export CDM_SECRET_KEY="SK..."
export CDM_DEVICE_ID="0004236908"   # optional, มี default อยู่แล้ว

php example.php
```

## Self-check (ไม่ต้องมี API key)

```bash
php selfcheck.php
```

ตรวจแค่ logic การสร้าง URL ไม่ยิง network จริง
