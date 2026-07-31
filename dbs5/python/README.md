# Python

Stdlib only (`urllib`) — ไม่ต้อง `pip install` อะไรเลย

## Requirements

- Python 3.8+

## Run

```bash
export CDM_ACCESS_KEY="AK..."
export CDM_SECRET_KEY="SK..."
export CDM_DEVICE_ID="0004236908"   # optional, มี default อยู่แล้ว

python example.py
```

## Self-check (ไม่ต้องมี API key)

```bash
python cdmdata_client.py
```

ตรวจแค่ logic การสร้าง URL/headers ไม่ยิง network จริง
