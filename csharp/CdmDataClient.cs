using System.Text;
using System.Text.Json;

namespace CdmDataSdk;

// Minimal CDM Data API client. Uses only BCL types (HttpClient, System.Text.Json),
// no NuGet package needed.

public class CdmDataException : Exception
{
    public int? StatusCode { get; }
    public string? Body { get; }

    public CdmDataException(string message, int? statusCode = null, string? body = null) : base(message)
    {
        StatusCode = statusCode;
        Body = body;
    }
}

public class CdmDataClient
{
    private readonly HttpClient _http;
    private readonly string _accessKey;
    private readonly string _secretKey;
    private readonly string _baseUrl;

    public CdmDataClient(string accessKey, string secretKey, string baseUrl = "https://dbs5.cplservice.com", HttpClient? httpClient = null)
    {
        _accessKey = accessKey;
        _secretKey = secretKey;
        _baseUrl = baseUrl.TrimEnd('/');
        _http = httpClient ?? new HttpClient();
    }

    // Pure URL building, kept separate so it can be self-checked without HTTP mocking.
    public static string BuildQueryUrl(string baseUrl, string path, Dictionary<string, string?>? query = null)
    {
        var url = baseUrl.TrimEnd('/') + path;
        if (query is { Count: > 0 })
        {
            var pairs = query
                .Where(kv => kv.Value is not null)
                .Select(kv => $"{Uri.EscapeDataString(kv.Key)}={Uri.EscapeDataString(kv.Value!)}");
            var qs = string.Join("&", pairs);
            if (qs.Length > 0) url += "?" + qs;
        }
        return url;
    }

    private async Task<JsonDocument> RequestAsync(HttpMethod method, string path, Dictionary<string, string?>? query = null, object? jsonBody = null)
    {
        var url = BuildQueryUrl(_baseUrl, path, query);

        using var req = new HttpRequestMessage(method, url);
        req.Headers.Add("x-api-access-key", _accessKey);
        req.Headers.Add("x-api-secret-key", _secretKey);

        if (jsonBody is not null)
        {
            var json = JsonSerializer.Serialize(jsonBody);
            req.Content = new StringContent(json, Encoding.UTF8, "application/json");
        }

        using var res = await _http.SendAsync(req);
        var text = await res.Content.ReadAsStringAsync();
        if (!res.IsSuccessStatusCode)
        {
            throw new CdmDataException($"HTTP {(int)res.StatusCode}", (int)res.StatusCode, text);
        }
        return JsonDocument.Parse(text);
    }

    public Task<JsonDocument> GetDataAsync(string deviceId, Dictionary<string, string?>? extraParams = null)
        => RequestAsync(HttpMethod.Get, "/cdmdata/v1/getdata", MergeDeviceId(deviceId, extraParams));

    public Task<JsonDocument> GetDataHourlyAsync(string deviceId, Dictionary<string, string?>? extraParams = null)
        => RequestAsync(HttpMethod.Get, "/cdmdata/v1/getdatahourly", MergeDeviceId(deviceId, extraParams));

    public Task<JsonDocument> GetDataDailyAsync(string deviceId, Dictionary<string, string?>? extraParams = null)
        => RequestAsync(HttpMethod.Get, "/cdmdata/v1/getdatadaily", MergeDeviceId(deviceId, extraParams));

    public Task<JsonDocument> OnOffCommandAsync(string deviceId, string meterId, string command)
    {
        if (command != "on" && command != "off")
            throw new ArgumentException("command must be 'on' or 'off'");

        return RequestAsync(HttpMethod.Post, "/cdmdata/v1/on-off-command", jsonBody: new
        {
            device_id = deviceId,
            meter_id = meterId,
            command
        });
    }

    private static Dictionary<string, string?> MergeDeviceId(string deviceId, Dictionary<string, string?>? extra)
    {
        var result = new Dictionary<string, string?> { ["device_id"] = deviceId };
        if (extra != null)
        {
            foreach (var kv in extra) result[kv.Key] = kv.Value;
        }
        return result;
    }
}
