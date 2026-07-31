# CDM Data API - Public Version

เอกสารสำหรับ user หรือระบบภายนอกที่ได้รับ `AK/SK` แล้ว เพื่ออ่านข้อมูลจาก CDM Kit Data API

`{{base_url}} = https://dbs5.cplservice.com`

Alternative host:

```text
https://dbs5.cplservice.net
```

## Authentication

ทุก request ต้องส่ง header:

```http
x-api-access-key: AK...
x-api-secret-key: SK...
```

ถ้า key ถูก revoke, inactive, หรือ secret ไม่ถูกต้อง จะได้:

```json
{
  "status": false,
  "message": "Invalid API key"
}
```

## Endpoints

```http
GET /cdmdata/v1/getdata
GET /cdmdata/v1/getdatahourly
GET /cdmdata/v1/getdatadaily
POST /cdmdata/v1/on-off-command
```

Mapping collection:

- `getdata` อ่านจาก `cdm_kit_data`
- `getdatahourly` อ่านจาก `cdm_kit_data_hourly`
- `getdatadaily` อ่านจาก `cdm_kit_data_daily`

## Required Query

- `device_id` required

ถ้าไม่ส่ง `device_id`:

```json
{
  "status": false,
  "message": "device_id is required"
}
```

ถ้า key ถูกจำกัด device และเรียก device ที่ไม่ได้รับอนุญาต:

```json
{
  "status": false,
  "message": "Device is not allowed for this API key"
}
```

สำหรับ on/off command ใช้ policy เดียวกัน:

- `allowed_devices: []` หรือไม่ส่ง = เรียก command ไม่ได้
- `allowed_devices: ["*"]` = เรียก command ได้ทุก `device_id`
- ระบุ device list = เรียก command ได้เฉพาะ `device_id` ใน list

## Query Parameters

- `device_id` required
- `from_time` filter `datetime >= from_time`
- `to_time` filter `datetime <= to_time`
- `from` filter `date >= from`
- `to` filter `date <= to`
- `page` default `1`, รองรับทั้ง `page=1` และ legacy `page=1,100`
- `limit` default `100`, max `1000`
- `order=field,asc|desc`, default `datetime,desc` และ `_id,desc`
- `column=field1,field2` เลือก field ที่ต้องการ
- `transform=1` คืน records เป็น array object
- `format=csv` หรือ `download=1` ดาวน์โหลด CSV
- `filter=field,op,value` ใช้ filter เพิ่มเติม

Supported filter operators:

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

## Examples

### Latest Raw Data

```bash
curl "{{base_url}}/cdmdata/v1/getdata?device_id=d3f6457e-0a54-44d9-bd9a-ef8708ad701c&limit=1&transform=1" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Latest Hourly Data

```bash
curl "{{base_url}}/cdmdata/v1/getdatahourly?device_id=0004236908&limit=1&transform=1" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Latest Daily Data

```bash
curl "{{base_url}}/cdmdata/v1/getdatadaily?device_id=0002855176&limit=1&transform=1" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Date Range

```bash
curl "{{base_url}}/cdmdata/v1/getdatahourly?device_id=0004236908&from=2025-06-01&to=2025-06-30&limit=500&order=datetime,asc&transform=1" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### Select Columns

```bash
curl "{{base_url}}/cdmdata/v1/getdatadaily?device_id=0002855176&column=device_id,datetime,date,time,data&limit=100&transform=1" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..."
```

### CSV Download

```bash
curl "{{base_url}}/cdmdata/v1/getdatahourly?device_id=0004236908&limit=100&format=csv" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..." \
  -o cdm-hourly.csv
```

### On-Off Command

```bash
curl -X POST "{{base_url}}/cdmdata/v1/on-off-command" \
  -H "content-type: application/json" \
  -H "x-api-access-key: AK..." \
  -H "x-api-secret-key: SK..." \
  -d '{
    "device_id": "0004236908",
    "meter_id": "01",
    "command": "on"
  }'
```

`command` ต้องเป็น `on` หรือ `off`

## JSON Response

```json
{
  "status": true,
  "body": {
    "function_name": "getdatahourly",
    "collection_name": "cdm_kit_data_hourly",
    "device_id": "0004236908",
    "page": 1,
    "limit": 1,
    "records": [
      {
        "_id": 2904136,
        "device_id": "0004236908",
        "datetime": "2025-06-25T11:49:05.002+0700",
        "date": "2025-06-25",
        "time": "11:49:05",
        "data": {}
      }
    ]
  }
}
```

## Cache

ระบบใช้ Redis cache TTL 15 วินาที

Response header:

- `X-Cache: MISS`
- `X-Cache: HIT`

## Usage Metering

ทุก request ที่ผ่าน AK/SK จะถูกบันทึก usage:

- API key id และ access key
- `device_id`
- function name
- collection name
- method/path/status code
- rows returned
- bytes in/out
- duration
- IP และ user-agent
- timestamp

ผู้ดูแลระบบดู usage ผ่าน endpoint:

```http
GET /cdmdata/v1/api-keys/:id/usage
```
