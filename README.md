# cdmdata-http-sdk

ตัวอย่างการเรียกใช้ CDM Data API ในแต่ละภาษา ทุกตัวอย่างใช้แค่ standard library / built-in ของแต่ละภาษา
**ไม่มี 3rd-party dependency ให้ต้องติดตั้ง**

Repo นี้ครอบคลุม API 2 รุ่น ที่เป็นคนละระบบกัน แยกโฟลเดอร์ชัดเจนตาม host:

| รุ่น | โฟลเดอร์ | Host | Query key | เอกสาร |
|---|---|---|---|---|
| ปัจจุบัน | [`dbs5/`](./dbs5) | `dbs5.cplservice.com` | `device_id` | [`dbs5/cdmdata-public-api.md`](./dbs5/cdmdata-public-api.md) |
| Legacy | [`dbs7/`](./dbs7) | `dbs7.cplservice.com` | `cust_id` | [`(legacy)cdmdata-public-api.md`](../(legacy)cdmdata-public-api.md) |

AK/SK ของสองระบบนี้**ไม่ใช่ key ตัวเดียวกัน** ต้องขอแยกกันจากผู้ดูแลระบบของแต่ละระบบ

ในแต่ละ `dbs5/<lang>` และ `dbs7/<lang>` มี:

- client เล็กๆ ที่ครอบ endpoint ของ API รุ่นนั้น
- `example.*` เรียกใช้งานจริงผ่าน env var (`CDM_ACCESS_KEY`, `CDM_SECRET_KEY`, และ `CDM_DEVICE_ID` หรือ `CDM_CUST_ID` แล้วแต่รุ่น)
- self-check ที่ตรวจ logic การสร้าง URL/headers โดยไม่ยิง network จริง (ไม่ต้องมี API key)
- README ของตัวเองบอกวิธีรัน

| ภาษา | ใช้อะไรยิง HTTP | ติดตั้งอะไรเพิ่ม |
|---|---|---|
| Python | `urllib` (stdlib) | ไม่ต้อง (Python 3.8+) |
| Node.js | `fetch` built-in | ไม่ต้อง (Node.js 18+) |
| PHP | `curl` extension | ไม่ต้อง (PHP 7.4+ + ext-curl) |
| Browser (HTML/JS) | `fetch` built-in | ไม่ต้อง เปิดไฟล์ในเบราว์เซอร์ได้เลย |
| C# | `HttpClient` (BCL) | ไม่ต้อง (.NET 8 SDK) |

## เริ่มต้นเร็วๆ

1. เช็คก่อนว่า device/มิเตอร์ที่จะอ่าน อยู่ในระบบไหน — มี `device_id` (ระบบใหม่, ดู `dbs5/`) หรือมี `cust_id` (ระบบ legacy, ดู `dbs7/`)
2. ขอ `x-api-access-key` (AK) และ `x-api-secret-key` (SK) ของระบบนั้นจากผู้ดูแลระบบ
3. เลือกโฟลเดอร์ `dbs5/<lang>` หรือ `dbs7/<lang>` ตามภาษาที่ต้องการ แล้วอ่าน README ในนั้น
4. รัน self-check ก่อนได้เพื่อเช็คว่า client เขียนถูก โดยไม่ต้องใช้ key จริง
5. ใส่ AK/SK แล้วรัน example จริง

## MCP / Agent tooling

ถ้าอยากให้ agent (เช่น Claude Code) เรียก API พวกนี้แทนการเขียนสคริปต์เอง ใช้
[`cdmdata-http-wrapper`](https://github.com/kisrkk/cdmdata-http-wrapper) — MCP server ตัวเดียวที่มี tools
ครบทั้งสองระบบ (`cdmdata_*` สำหรับ dbs5, `cdmdata_legacy_*` สำหรับ dbs7) พร้อม Agent Skill ที่สอนให้ agent
เข้าใจ endpoint/parameter ของแต่ละระบบทันทีโดยไม่ต้องอ่านเอกสารนี้

## Error handling (ทดสอบกับ server จริงแล้ว)

ทุก client จะ throw exception ที่มี status code + response body ให้เช็คได้ (`CdmDataError` /
`CdmDataException` แล้วแต่ภาษา)

ค่าที่เจอจริงจาก `dbs5.cplservice.com` (ระบบใหม่, `device_id`):

| กรณี | HTTP status | message |
|---|---|---|
| ไม่ส่ง header AK/SK มาเลย | `401` | `API key is required` |
| ส่ง AK/SK แต่ผิด/ถูก revoke | `403` | `Invalid API key` |
| ไม่ส่ง `device_id` | `403` | `device_id is required` (ตาม docs; ถ้า key ผิดด้วยจะเจอ `Invalid API key` ก่อน) |

ค่าที่เจอจริงจาก `dbs7.cplservice.com` (ระบบ legacy, `cust_id`):

| กรณี | message |
|---|---|
| ส่ง AK/SK แต่ผิด/ถูก revoke | `Invalid API key` |
| ไม่ส่ง header AK/SK มาเลย | `401` |
| ไม่ส่ง `cust_id` | `cust_id is required` |
| `cust_id` ไม่อยู่ใน `allowed_devices` ของ key | `This cust_id is not allowed for this API key` |

⚠️ **Gotcha ที่เจอจริงระหว่างเทส:** Cloudflare หน้า origin จะบล็อก request ที่ใช้ User-Agent
เริ่มต้นของ `urllib` (`Python-urllib/x.y`) ว่าเป็น bot แล้วตอบกลับเป็น plain text (`error code: 1010`)
แทนที่จะเป็น JSON — Python client ใน repo นี้ตั้ง `User-Agent: cdmdata-http-sdk-python/1.0` ให้เองแล้วเพื่อเลี่ยงปัญหานี้
ถ้าเขียน client เองด้วย `urllib`/เครื่องมืออื่นที่ไม่ได้ตั้ง UA ให้ระวังจุดนี้ไว้ด้วย

## ข้อควรระวังอื่นๆ

- **อย่าฝัง secret key ไว้ใน client-side JS ของเว็บ public** (ดูคำเตือนใน `html-js/README.md` ของแต่ละรุ่น) —
  ตัวอย่าง browser เหมาะกับ internal tool/demo เท่านั้น
- endpoint `GET /cdmdata/v1/api-keys/:id/usage` เป็น internal endpoint สำหรับ web master ไม่ได้ใช้ AK/SK
  แบบเดียวกับ endpoint อื่น จึงไม่รวมอยู่ใน SDK ตัวอย่างนี้
- `dbs5/*` รองรับ query parameter เพิ่มเติมที่ `dbs7/*` ไม่มี: `transform`, `column`, `format`, `download`
  (ดูรายละเอียดในเอกสารของแต่ละรุ่น) — ทุก client รับ extra params/kwargs เพิ่มได้ตามชื่อ field ตรงๆ อยู่แล้ว
- PHP บน Windows บางเครื่องเจอ `SSL certificate problem: unable to get local issuer certificate` เพราะ
  php.ini ไม่ได้ตั้ง `curl.cainfo` ชี้ไปที่ CA bundle — เป็นปัญหา environment ไม่ใช่บั๊กโค้ด แก้โดยดาวน์โหลด
  `cacert.pem` จาก https://curl.se/ca/cacert.pem แล้วตั้ง `curl.cainfo = "C:\path\to\cacert.pem"` ใน php.ini
