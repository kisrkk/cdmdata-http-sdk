# CDM Data API - Public Version (Legacy / etrix-server-aws-ts)

เอกสารสำหรับ user หรือระบบภายนอกที่ได้รับ `AK/SK` แล้ว เพื่ออ่านข้อมูลไฟฟ้า/น้ำ และสั่ง on-off มิเตอร์รุ่น remote-cutoff
จากระบบ legacy นี้ (ขอ AK/SK ได้จาก Web master ผ่าน [cdmdata-internal-api.md](./cdmdata-internal-api.md))

`{{base_url}} = https://dbs7.cplservice.com`

## Authentication

ทุก request ต้องส่ง header:

```http
x-api-access-key: AK...
x-api-secret-key: SK...
```

ถ้า key ถูก revoke, inactive, หรือ secret ไม่ถูกต้อง จะได้:

```json
{ "status": false, "message": "Invalid API key" }
```

ถ้าไม่ส่ง header เลย จะได้ `401`:

```json
{ "status": false, "message": "Missing x-api-access-key / x-api-secret-key" }
```

## Endpoints

ระบบนี้แยกน้ำออกจากไฟฟ้าอย่างชัดเจน แต่ละ endpoint ผูกกับ Prisma model ตัวเดียว:

| Method | Path                        | Prisma Model    | ความหมาย                                  |
| ------ | --------------------------- | ---------------- | ------------------------------------------ |
| GET    | `/cdmdata/v1/getdata`              | `electric`       | kWh Raw Data                               |
| GET    | `/cdmdata/v1/electric1hour`        | `electric1hour`  | kWh Data รายชั่วโมง                        |
| GET    | `/cdmdata/v1/getdatadaily`         | `electric1day`   | kWh Data รายวัน                            |
| GET    | `/cdmdata/v1/getdata_water`        | `water`          | m³ Raw Data                                |
| GET    | `/cdmdata/v1/getdata1hour_water`   | `water1hour`     | m³ Data รายชั่วโมง                         |
| GET    | `/cdmdata/v1/getdatadaily_water`   | `water1day`      | m³ Data รายวัน                             |
| GET    | `/cdmdata/v1/on-off-command`       | `address`        | อ่านสถานะ on/off ของมิเตอร์ (feedback)     |
| POST   | `/cdmdata/v1/on-off-command`       | `address`        | สั่ง on/off มิเตอร์ไฟฟ้า remote-cutoff     |

## Required Query

- `cust_id` required ทุก endpoint (รวม `on-off-command`)

ถ้าไม่ส่ง `cust_id`:

```json
{ "status": false, "message": "cust_id is required" }
```

ถ้า key ถูกจำกัด `cust_id` และเรียก `cust_id` ที่ไม่ได้รับอนุญาต:

```json
{ "status": false, "message": "This cust_id is not allowed for this API key" }
```

Policy ของ `allowed_devices` ใช้ค่า `cust_id` เดียวกันทุก endpoint รวมถึง on/off command:

- `allowed_devices: []` หรือไม่ส่ง = เรียก endpoint ไหนก็ไม่ได้เลย
- `allowed_devices: ["*"]` = เรียกได้ทุก `cust_id`
- ระบุ list เช่น `["270000005"]` = เรียกได้เฉพาะ `cust_id` ใน list

## Query Parameters (GET endpoints)

- `cust_id` required
- `from` filter `tb_date >= from` (เฉพาะ endpoint ข้อมูลไฟฟ้า/น้ำ, ไม่มีผลกับ `on-off-command`)
- `to` filter `tb_date <= to`
- `from_time` filter `tb_datetime >= from_time`
- `to_time` filter `tb_datetime <= to_time`
- `page` default `1`
- `limit` default `100`, max `1000`
- `order=field,asc|desc` default `tb_datetime,desc` (endpoint ข้อมูลไฟฟ้า/น้ำ) หรือ `cpl_rec_id,desc` (`on-off-command`)
- `filter=field,op,value` ใช้ filter เพิ่มเติมได้ (ส่งซ้ำได้หลายตัว), รวมกับ `cust_id` ด้วย AND เสมอ
- `satisfy=any` เปลี่ยน filter เพิ่มเติมให้รวมกันด้วย OR แทน AND (ไม่กระทบเงื่อนไข `cust_id`)

Supported filter operators (field ต้องเป็นคอลัมน์จริงใน model นั้น ๆ):

- `cs` contains
- `sw` starts with
- `ew` ends with
- `eq` equals
- `ne` not equals
- `lt` less than
- `le` less than or equals
- `ge` greater than or equals
- `gt` greater than
- `in` in list
- `ni` not in list
- `is` is null
- `no` is not null
- `bt` between (ต้องมี 2 ค่า คั่นด้วย comma)

## Examples

### Latest Raw Data (ไฟฟ้า)

```bash
curl "{{base_url}}/cdmdata/v1/getdata?cust_id=270000005&limit=1" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Latest Hourly Data (ไฟฟ้า)

```bash
curl "{{base_url}}/cdmdata/v1/electric1hour?cust_id=270000005&limit=1" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Latest Daily Data (น้ำ)

```bash
curl "{{base_url}}/cdmdata/v1/getdatadaily_water?cust_id=100000004&limit=1" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Date Range

```bash
curl "{{base_url}}/cdmdata/v1/getdatadaily?cust_id=270000005&from=2026-07-01&to=2026-07-31&limit=500&order=tb_datetime,asc" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Extra Filter

```bash
curl "{{base_url}}/cdmdata/v1/getdata_water?cust_id=100000004&filter=tb_status1,eq,0&limit=50" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Read On-Off Status

```bash
curl "{{base_url}}/cdmdata/v1/on-off-command?cust_id=270000005" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Send On-Off Command

```bash
curl -X POST "{{base_url}}/cdmdata/v1/on-off-command" \
  -H "content-type: application/json" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..." \
  -d '{
    "cust_id": "270000005",
    "meter_address": "NS8888666796",
    "command": "off"
  }'
```

`command` ต้องเป็น `on` หรือ `off` (ไม่สนตัวพิมพ์เล็ก/ใหญ่) `meter_address` และ `meter_id` เป็น optional ใช้เจาะจงมิเตอร์ตัวใดตัวหนึ่งภายใต้ `cust_id` เดียวกัน ถ้าไม่ส่งจะสั่งทุกมิเตอร์ของ `cust_id` นั้น
คำสั่งจะเขียนลงคอลัมน์ `status_on_off` ในตาราง `address` ทันที ส่วน `status_feedback_on_off` เป็นค่าที่ตัวมิเตอร์รายงานกลับมาเอง (อ่านอย่างเดียวผ่าน API นี้)

## JSON Response

Data endpoints (ไฟฟ้า/น้ำ):

```json
{
  "status": true,
  "body": {
    "function_name": "getdata",
    "collection_name": "electric",
    "cust_id": "270000005",
    "page": 1,
    "limit": 1,
    "records": [
      {
        "cpl_post_id": 400846048,
        "cust_id": 270000005,
        "building": "A",
        "tb_datetime": "2026-07-31 17:56:51",
        "tb_date": "2026-07-31 00:00:00",
        "tb_time": "17:56:51",
        "tb_kWh1": null,
        "tb_status1": null
      }
    ]
  }
}
```

`on-off-command` (ทั้ง GET และ POST):

```json
{
  "status": true,
  "body": {
    "function_name": "on-off-command",
    "collection_name": "address",
    "cust_id": "270000005",
    "records": [
      {
        "cpl_rec_id": 18384,
        "cust_id": 270000005,
        "room": "T01",
        "meter_address": "NS8888666796",
        "meter_last_unit": null,
        "meter_id": 33,
        "status_on_off": "off",
        "status_feedback_on_off": ""
      }
    ]
  }
}
```

## Usage Metering

ทุก request ที่ผ่าน AK/SK ที่มีอยู่จริง (ต่อให้ auth fail ในขั้นตอนหลังจากนั้น เช่น secret ผิด/`cust_id` ไม่ได้รับอนุญาต) จะถูกบันทึกลง `cdmdata_api_usage_events`:

- API key id และ access key
- `cust_id`
- function name / collection name
- method / path / status code
- rows returned
- bytes in/out
- duration
- IP และ user-agent
- timestamp (`occurred_at`)

Web master ดู usage ผ่าน Internal endpoint:

```http
GET /cdmdata/v1/api-keys/:id/usage
```

รายละเอียดดูที่ [cdmdata-internal-api.md](./cdmdata-internal-api.md)

## Tooling (SDK / MCP)

มี tooling สำเร็จรูปสำหรับ API นี้ 2 ทาง — ทั้งคู่แยกต่างหากจาก tooling ของ API รุ่นใหม่ (`dbs5.cplservice.com`,
`device_id`) เพราะเป็นคนละระบบกัน **AK/SK ก็คนละชุดกัน ห้ามใช้ปนกัน**

### MCP server (สำหรับ agent เช่น Claude Code)

[`cdmdata-http-wrapper`](https://github.com/kisrkk/cdmdata-http-wrapper) มี tool ชุด `cdmdata_legacy_*`
ที่คุยกับ API นี้โดยตรง — agent เรียกใช้ได้เลยโดยไม่ต้องเขียน HTTP request เอง:

| Tool | Endpoint |
|------|----------|
| `cdmdata_legacy_getdata` | `GET /cdmdata/v1/getdata` (ไฟฟ้า raw) |
| `cdmdata_legacy_getdata_hourly` | `GET /cdmdata/v1/electric1hour` |
| `cdmdata_legacy_getdata_daily` | `GET /cdmdata/v1/getdatadaily` |
| `cdmdata_legacy_getdata_water` | `GET /cdmdata/v1/getdata_water` |
| `cdmdata_legacy_getdata_water_hourly` | `GET /cdmdata/v1/getdata1hour_water` |
| `cdmdata_legacy_getdata_water_daily` | `GET /cdmdata/v1/getdatadaily_water` |
| `cdmdata_legacy_onoff_status` | `GET /cdmdata/v1/on-off-command` |
| `cdmdata_legacy_onoff_command` | `POST /cdmdata/v1/on-off-command` |

ต้องตั้ง env `CDMDATA_LEGACY_ACCESS_KEY` / `CDMDATA_LEGACY_SECRET_KEY` ตอน register MCP server (แยกจาก
`CDMDATA_ACCESS_KEY`/`CDMDATA_SECRET_KEY` ที่เป็นของ API รุ่นใหม่) รายละเอียดวิธี register และ Agent Skill
(`skills/cdmdata/SKILL.md`) ที่สอน agent ให้เข้าใจ parameter/response ของ endpoint พวกนี้ทันที ดูที่ README
ของ repo ด้านบน

### SDK ตัวอย่างต่อภาษา (ไม่มี 3rd-party dependency)

[`cdmdata-http-sdk/dbs7/`](https://github.com/kisrkk/cdmdata-http-sdk/tree/main/dbs7) มี client + example +
self-check สำหรับ API นี้โดยเฉพาะ แยกภาษา: `python/`, `nodejs/`, `php/`, `html-js/`, `csharp/` — ใช้แค่
stdlib/built-in ของแต่ละภาษา (`urllib`, `fetch`, `curl` extension, `HttpClient`) ไม่ต้องติดตั้งอะไรเพิ่ม
ถ้าต้องการ API รุ่นใหม่ (`device_id`) ดูที่ [`cdmdata-http-sdk/dbs5/`](https://github.com/kisrkk/cdmdata-http-sdk/tree/main/dbs5) แทน
