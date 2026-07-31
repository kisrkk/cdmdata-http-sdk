<?php

// Minimal CDM Data API client (legacy / dbs7, cust_id-based). Uses ext-curl (bundled with PHP), no Composer needed.

class CdmDataException extends Exception
{
    public $statusCode;
    public $body;

    public function __construct($message, $statusCode = null, $body = null)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->body = $body;
    }
}

class CdmDataClient
{
    private $accessKey;
    private $secretKey;
    private $baseUrl;

    public function __construct(string $accessKey, string $secretKey, string $baseUrl = 'https://dbs7.cplservice.com')
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    // Pure URL building, kept separate so it can be self-checked without curl/network.
    public static function buildUrl(string $baseUrl, string $path, array $params = []): string
    {
        $url = rtrim($baseUrl, '/') . $path;
        $params = array_filter($params, function ($v) {
            return $v !== null;
        });
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    private function request(string $method, string $path, array $params = [], ?array $jsonBody = null): array
    {
        $url = self::buildUrl($this->baseUrl, $path, $params);

        $headers = [
            'x-api-access-key: ' . $this->accessKey,
            'x-api-secret-key: ' . $this->secretKey,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($jsonBody !== null) {
            $headers[] = 'content-type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonBody));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new CdmDataException("cURL error: $error");
        }
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($statusCode >= 400) {
            throw new CdmDataException("HTTP $statusCode", $statusCode, $data);
        }
        return $data;
    }

    // ไฟฟ้า
    public function getData(string $custId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/getdata', ['cust_id' => $custId] + $params);
    }

    public function getElectricHourly(string $custId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/electric1hour', ['cust_id' => $custId] + $params);
    }

    public function getDataDaily(string $custId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/getdatadaily', ['cust_id' => $custId] + $params);
    }

    // น้ำ
    public function getWater(string $custId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/getdata_water', ['cust_id' => $custId] + $params);
    }

    public function getWaterHourly(string $custId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/getdata1hour_water', ['cust_id' => $custId] + $params);
    }

    public function getWaterDaily(string $custId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/getdatadaily_water', ['cust_id' => $custId] + $params);
    }

    // on/off (remote-cutoff)
    public function getOnOffStatus(string $custId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/on-off-command', ['cust_id' => $custId] + $params);
    }

    public function onOffCommand(string $custId, string $command, ?string $meterAddress = null, ?string $meterId = null): array
    {
        if (!in_array($command, ['on', 'off'], true)) {
            throw new InvalidArgumentException("command must be 'on' or 'off'");
        }
        $body = ['cust_id' => $custId, 'command' => $command];
        if ($meterAddress !== null) {
            $body['meter_address'] = $meterAddress;
        }
        if ($meterId !== null) {
            $body['meter_id'] = $meterId;
        }
        return $this->request('POST', '/cdmdata/v1/on-off-command', [], $body);
    }
}
