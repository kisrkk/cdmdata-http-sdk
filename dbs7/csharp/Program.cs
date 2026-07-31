using CdmDataSdk;

SelfCheck();

var accessKey = Environment.GetEnvironmentVariable("CDM_ACCESS_KEY") ?? "AK...";
var secretKey = Environment.GetEnvironmentVariable("CDM_SECRET_KEY") ?? "SK...";
var custId = Environment.GetEnvironmentVariable("CDM_CUST_ID") ?? "270000005";

var client = new CdmDataClient(accessKey, secretKey);

try
{
    // 1) ข้อมูลไฟฟ้ารายวันล่าสุด 1 record
    var latest = await client.GetDataDailyAsync(custId, new() { ["limit"] = "1" });
    Console.WriteLine(latest.RootElement);

    // 2) ข้อมูลช่วงวันที่
    var range = await client.GetDataDailyAsync(custId, new()
    {
        ["from"] = "2026-07-01",
        ["to"] = "2026-07-31",
        ["limit"] = "500",
        ["order"] = "tb_datetime,asc",
    });
    var records = range.RootElement.GetProperty("body").GetProperty("records");
    Console.WriteLine($"{records.GetArrayLength()} records");

    // 3) อ่านสถานะ on/off ปัจจุบัน
    var status = await client.GetOnOffStatusAsync(custId);
    Console.WriteLine(status.RootElement);

    // 4) สั่งเปิด/ปิดมิเตอร์ตัวใดตัวหนึ่ง (ต้องมี allowed_devices ครอบคลุม cust_id นี้)
    // var result = await client.OnOffCommandAsync(custId, "off", meterAddress: "NS8888666796");
    // Console.WriteLine(result.RootElement);
}
catch (CdmDataException ex)
{
    Console.Error.WriteLine($"CDM Data API error ({ex.StatusCode}): {ex.Body}");
    Environment.Exit(1);
}

// ponytail: self-check for URL building only, no real network call
static void SelfCheck()
{
    var url = CdmDataClient.BuildQueryUrl("https://example.com", "/path", new Dictionary<string, string?>
    {
        ["cust_id"] = "270000005",
        ["limit"] = "1",
        ["skip_me"] = null,
    });
    if (!url.Contains("cust_id=270000005")) throw new Exception("self-check failed: cust_id missing");
    if (!url.Contains("limit=1")) throw new Exception("self-check failed: limit missing");
    if (url.Contains("skip_me")) throw new Exception("self-check failed: null param not skipped");
    Console.WriteLine("CdmDataClient (dbs7 legacy) self-check OK");
}
