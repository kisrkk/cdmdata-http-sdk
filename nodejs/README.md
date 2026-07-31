# Node.js

ใช้ `fetch` ที่ built-in มากับ Node.js >=18 — ไม่ต้อง `npm install` อะไรเลย

## Requirements

- Node.js 18+

## Run

```bash
export CDM_ACCESS_KEY="AK..."
export CDM_SECRET_KEY="SK..."
export CDM_DEVICE_ID="0004236908"   # optional, มี default อยู่แล้ว

npm start
```

## Self-check (ไม่ต้องมี API key)

```bash
npm run selfcheck
```

ตรวจแค่ logic การสร้าง URL/headers ไม่ยิง network จริง
