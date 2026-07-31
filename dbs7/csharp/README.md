# C# (legacy / dbs7)

ใช้แค่ BCL (`HttpClient`, `System.Text.Json`) — ไม่ต้องลง NuGet package ใดๆ ใช้กับ [legacy API](../../../(legacy)cdmdata-public-api.md)
(`cust_id`, ไฟฟ้า/น้ำ, `dbs7.cplservice.com`) — ถ้าต้องการ API รุ่นใหม่ (`device_id`) ดูที่ [`../../dbs5/csharp`](../../dbs5/csharp)

## Requirements

- .NET 8 SDK

## Run

```powershell
$env:CDM_ACCESS_KEY = "AK..."
$env:CDM_SECRET_KEY = "SK..."
$env:CDM_CUST_ID = "270000005"   # optional, มี default อยู่แล้ว

dotnet run
```

โปรแกรมจะรัน self-check (ตรวจ logic สร้าง URL ไม่ยิง network จริง) ก่อนเสมอ แล้วค่อยเรียก API จริงต่อ
