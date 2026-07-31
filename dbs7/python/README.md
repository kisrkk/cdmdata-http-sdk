# Python (legacy / dbs7)

Stdlib only (`urllib`) — ไม่ต้อง `pip install` อะไรเลย ใช้กับ [legacy API](../../../(legacy)cdmdata-public-api.md)
(`cust_id`, ไฟฟ้า/น้ำ, `dbs7.cplservice.com`) — ถ้าต้องการ API รุ่นใหม่ (`device_id`) ดูที่ [`../../dbs5/python`](../../dbs5/python)

## Requirements

- Python 3.8+

## Run

```bash
export CDM_ACCESS_KEY="AK..."
export CDM_SECRET_KEY="SK..."
export CDM_CUST_ID="270000005"   # optional, มี default อยู่แล้ว

python example.py
```

## Self-check (ไม่ต้องมี API key)

```bash
python cdmdata_client.py
```

ตรวจแค่ logic การสร้าง URL/headers ไม่ยิง network จริง
