# cdmdata-http-sdk

ตัวอย่างการเรียกใช้ [CDM Data API](./cdmdata-public-api.md) ในแต่ละภาษา ทุกตัวอย่างใช้แค่
standard library / built-in ของแต่ละภาษา **ไม่มี 3rd-party dependency ให้ต้องติดตั้ง**

| ภาษา | โฟลเดอร์ | ใช้อะไรยิง HTTP | ติดตั้งอะไรเพิ่ม |
|---|---|---|---|
| Python | [`python/`](./python) | `urllib` (stdlib) | ไม่ต้อง (Python 3.8+) |
| Node.js | [`nodejs/`](./nodejs) | `fetch` built-in | ไม่ต้อง (Node.js 18+) |
| PHP | [`php/`](./php) | `curl` extension | ไม่ต้อง (PHP 7.4+ + ext-curl) |
| Browser (HTML/JS) | [`html-js/`](./html-js) | `fetch` built-in | ไม่ต้อง เปิดไฟล์ในเบราว์เซอร์ได้เลย |
| C# | [`csharp/`](./csharp) | `HttpClient` (BCL) | ไม่ต้อง (.NET 8 SDK) |

แต่ละโฟลเดอร์มี:

- client เล็กๆ ที่ครอบ endpoint `getdata` / `getdatahourly` / `getdatadaily` / `on-off-command`
- `example.*` เรียกใช้งานจริงผ่าน env var `CDM_ACCESS_KEY`, `CDM_SECRET_KEY`, `CDM_DEVICE_ID`
- self-check ที่ตรวจ logic การสร้าง URL/headers โดยไม่ยิง network จริง (ไม่ต้องมี API key)
- README ของตัวเองบอกวิธีรัน

## เริ่มต้นเร็วๆ

1. ขอ `x-api-access-key` (AK) และ `x-api-secret-key` (SK) จากผู้ดูแลระบบ
2. เลือกโฟลเดอร์ภาษาที่ต้องการ แล้วอ่าน README ในนั้น
3. รัน self-check ก่อนได้เพื่อเช็คว่า client เขียนถูก โดยไม่ต้องใช้ key จริง
4. ใส่ AK/SK แล้วรัน example จริง

## Error handling (ทดสอบกับ server จริงแล้ว)

ทุก client จะ throw exception ที่มี status code + response body ให้เช็คได้ (`CdmDataError` /
`CdmDataException` แล้วแต่ภาษา) ค่าที่เจอจริงจาก `dbs5.cplservice.com`:

| กรณี | HTTP status | message |
|---|---|---|
| ไม่ส่ง header AK/SK มาเลย | `401` | `API key is required` |
| ส่ง AK/SK แต่ผิด/ถูก revoke | `403` | `Invalid API key` |
| ไม่ส่ง `device_id` | `403` | `device_id is required` (ตาม docs; ถ้า key ผิดด้วยจะเจอ `Invalid API key` ก่อน) |

⚠️ **Gotcha ที่เจอจริงระหว่างเทส:** Cloudflare หน้า origin จะบล็อก request ที่ใช้ User-Agent
เริ่มต้นของ `urllib` (`Python-urllib/x.y`) ว่าเป็น bot แล้วตอบกลับเป็น plain text (`error code: 1010`)
แทนที่จะเป็น JSON — Python client ใน repo นี้ตั้ง `User-Agent: cdmdata-http-sdk-python/1.0` ให้เองแล้วเพื่อเลี่ยงปัญหานี้
ถ้าเขียน client เองด้วย `urllib`/เครื่องมืออื่นที่ไม่ได้ตั้ง UA ให้ระวังจุดนี้ไว้ด้วย

## ข้อควรระวังอื่นๆ

- **อย่าฝัง secret key ไว้ใน client-side JS ของเว็บ public** (ดูคำเตือนใน [`html-js/README.md`](./html-js/README.md)) —
  ตัวอย่าง browser เหมาะกับ internal tool/demo เท่านั้น
- endpoint `GET /cdmdata/v1/api-keys/:id/usage` เป็น internal endpoint สำหรับ web master ไม่ได้ใช้ AK/SK
  แบบเดียวกับ endpoint อื่น จึงไม่รวมอยู่ใน SDK ตัวอย่างนี้
- ทุก client รองรับเฉพาะ query parameter หลักตามเอกสาร (`from_time`, `to_time`, `from`, `to`, `page`, `limit`,
  `order`, `column`, `transform`, `format`, `download`, `filter`) — ส่งเป็น extra params/kwargs เพิ่มได้ตามชื่อ field ตรงๆ
- PHP บน Windows บางเครื่องเจอ `SSL certificate problem: unable to get local issuer certificate` เพราะ
  php.ini ไม่ได้ตั้ง `curl.cainfo` ชี้ไปที่ CA bundle — เป็นปัญหา environment ไม่ใช่บั๊กโค้ด แก้โดยดาวน์โหลด
  `cacert.pem` จาก https://curl.se/ca/cacert.pem แล้วตั้ง `curl.cainfo = "C:\path\to\cacert.pem"` ใน php.ini
