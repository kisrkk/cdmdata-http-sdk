using CdmDataSdk;

SelfCheck();

var accessKey = Environment.GetEnvironmentVariable("CDM_ACCESS_KEY") ?? "AK...";
var secretKey = Environment.GetEnvironmentVariable("CDM_SECRET_KEY") ?? "SK...";
var deviceId = Environment.GetEnvironmentVariable("CDM_DEVICE_ID") ?? "0004236908";

var client = new CdmDataClient(accessKey, secretKey);

try
{
    // 1) ข้อมูล hourly ล่าสุด 1 record
    var latest = await client.GetDataHourlyAsync(deviceId, new() { ["limit"] = "1", ["transform"] = "1" });
    Console.WriteLine(latest.RootElement);

    // 2) ข้อมูลช่วงวันที่
    var range = await client.GetDataHourlyAsync(deviceId, new()
    {
        ["from"] = "2025-06-01",
        ["to"] = "2025-06-30",
        ["limit"] = "500",
        ["order"] = "datetime,asc",
        ["transform"] = "1",
    });
    var records = range.RootElement.GetProperty("body").GetProperty("records");
    Console.WriteLine($"{records.GetArrayLength()} records");

    // 3) สั่งเปิด/ปิด (ต้องมี allowed_devices ครอบคลุม device_id นี้)
    // var result = await client.OnOffCommandAsync(deviceId, "01", "on");
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
        ["device_id"] = "abc123",
        ["limit"] = "1",
        ["skip_me"] = null,
    });
    if (!url.Contains("device_id=abc123")) throw new Exception("self-check failed: device_id missing");
    if (!url.Contains("limit=1")) throw new Exception("self-check failed: limit missing");
    if (url.Contains("skip_me")) throw new Exception("self-check failed: null param not skipped");
    Console.WriteLine("CdmDataClient self-check OK");
}
