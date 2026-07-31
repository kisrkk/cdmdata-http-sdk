import os
import sys

from cdmdata_client import CdmDataClient, CdmDataError

ACCESS_KEY = os.environ.get("CDM_ACCESS_KEY", "AK...")
SECRET_KEY = os.environ.get("CDM_SECRET_KEY", "SK...")
DEVICE_ID = os.environ.get("CDM_DEVICE_ID", "0004236908")

client = CdmDataClient(ACCESS_KEY, SECRET_KEY)

try:
    # 1) ข้อมูล hourly ล่าสุด 1 record
    latest = client.get_data_hourly(DEVICE_ID, limit=1, transform=1)
    print(latest)

    # 2) ข้อมูลช่วงวันที่ (from/to เป็น python keyword เลยต้องส่งผ่าน dict)
    range_data = client.get_data_hourly(
        DEVICE_ID,
        **{"from": "2025-06-01", "to": "2025-06-30"},
        limit=500,
        order="datetime,asc",
        transform=1,
    )
    print(len(range_data["body"]["records"]), "records")

    # 3) สั่งเปิด/ปิด (ต้องมี allowed_devices ครอบคลุม device_id นี้)
    # result = client.on_off_command(DEVICE_ID, meter_id="01", command="on")
    # print(result)
except CdmDataError as e:
    print(f"CDM Data API error ({e.status_code}): {e.body}", file=sys.stderr)
    sys.exit(1)
