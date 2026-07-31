# HTML / Browser JS

ใช้ `fetch` ของ browser ล้วนๆ ไม่มี library, ไม่มี build step

## Requirements

- ไม่ต้องติดตั้งอะไร แค่เปิดไฟล์ในเบราว์เซอร์ (หรือรันผ่าน local server เช่น `python -m http.server`)

## Run

```bash
# เปิดตรงๆ
start index.html        # Windows
# หรือรันผ่าน local server
python -m http.server -d . 8080
```

แล้วกรอก AK/SK/device_id ในฟอร์ม

## ⚠️ คำเตือนด้านความปลอดภัย

หน้านี้เหมาะกับ **internal tool / demo เท่านั้น** ถ้าเป็นเว็บ public ห้ามฝัง secret key (`x-api-secret-key`)
ไว้ใน JS ที่ฝั่ง client เด็ดขาด เพราะใครก็เปิด DevTools ดู network request แล้วขโมย key ไปได้
สำหรับ production ควรทำ backend proxy (เช่นใน Node/PHP/Python ในโฟลเดอร์อื่นของ repo นี้)
ให้ browser เรียก backend ของเราแทน แล้วให้ backend เก็บ AK/SK

นอกจากนี้ request ตรงจาก browser จะสำเร็จได้ก็ต่อเมื่อฝั่ง server เปิด CORS ให้ origin ที่เรียก
