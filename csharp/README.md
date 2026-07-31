# C#

ใช้แค่ BCL (`HttpClient`, `System.Text.Json`) — ไม่ต้องลง NuGet package ใดๆ

## Requirements

- .NET 8 SDK

## Run

```powershell
$env:CDM_ACCESS_KEY = "AK..."
$env:CDM_SECRET_KEY = "SK..."
$env:CDM_DEVICE_ID = "0004236908"   # optional, มี default อยู่แล้ว

dotnet run
```

โปรแกรมจะรัน self-check (ตรวจ logic สร้าง URL ไม่ยิง network จริง) ก่อนเสมอ แล้วค่อยเรียก API จริงต่อ
