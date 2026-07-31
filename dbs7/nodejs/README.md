# Node.js (legacy / dbs7)

ใช้ `fetch` ที่ built-in มากับ Node.js >=18 — ไม่ต้อง `npm install` อะไรเลย ใช้กับ [legacy API](../../../(legacy)cdmdata-public-api.md)
(`cust_id`, ไฟฟ้า/น้ำ, `dbs7.cplservice.com`) — ถ้าต้องการ API รุ่นใหม่ (`device_id`) ดูที่ [`../../dbs5/nodejs`](../../dbs5/nodejs)

## Requirements

- Node.js 18+

## Run

```bash
export CDM_ACCESS_KEY="AK..."
export CDM_SECRET_KEY="SK..."
export CDM_CUST_ID="270000005"   # optional, มี default อยู่แล้ว

npm start
```

## Self-check (ไม่ต้องมี API key)

```bash
npm run selfcheck
```

ตรวจแค่ logic การสร้าง URL/headers ไม่ยิง network จริง
