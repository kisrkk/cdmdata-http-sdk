import os
import sys

from cdmdata_client import CdmDataClient, CdmDataError

ACCESS_KEY = os.environ.get("CDM_ACCESS_KEY", "AK...")
SECRET_KEY = os.environ.get("CDM_SECRET_KEY", "SK...")
CUST_ID = os.environ.get("CDM_CUST_ID", "270000005")

client = CdmDataClient(ACCESS_KEY, SECRET_KEY)

try:
    # 1) ข้อมูลไฟฟ้ารายวันล่าสุด 1 record
    latest = client.get_data_daily(CUST_ID, limit=1)
    print(latest)

    # 2) ข้อมูลช่วงวันที่ (from/to เป็น python keyword เลยต้องส่งผ่าน dict)
    range_data = client.get_data_daily(
        CUST_ID,
        **{"from": "2026-07-01", "to": "2026-07-31"},
        limit=500,
        order="tb_datetime,asc",
    )
    print(len(range_data["body"]["records"]), "records")

    # 3) อ่านสถานะ on/off ปัจจุบัน
    status = client.get_on_off_status(CUST_ID)
    print(status)

    # 4) สั่งเปิด/ปิดมิเตอร์ตัวใดตัวหนึ่ง (ต้องมี allowed_devices ครอบคลุม cust_id นี้)
    # result = client.on_off_command(CUST_ID, "off", meter_address="NS8888666796")
    # print(result)
except CdmDataError as e:
    print(f"CDM Data API error ({e.status_code}): {e.body}", file=sys.stderr)
    sys.exit(1)
