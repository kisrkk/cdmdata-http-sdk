<?php

// Minimal CDM Data API client. Uses ext-curl (bundled with PHP), no Composer needed.

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

    public function __construct(string $accessKey, string $secretKey, string $baseUrl = 'https://dbs5.cplservice.com')
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

    public function getData(string $deviceId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/getdata', ['device_id' => $deviceId] + $params);
    }

    public function getDataHourly(string $deviceId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/getdatahourly', ['device_id' => $deviceId] + $params);
    }

    public function getDataDaily(string $deviceId, array $params = []): array
    {
        return $this->request('GET', '/cdmdata/v1/getdatadaily', ['device_id' => $deviceId] + $params);
    }

    public function onOffCommand(string $deviceId, string $meterId, string $command): array
    {
        if (!in_array($command, ['on', 'off'], true)) {
            throw new InvalidArgumentException("command must be 'on' or 'off'");
        }
        return $this->request('POST', '/cdmdata/v1/on-off-command', [], [
            'device_id' => $deviceId,
            'meter_id' => $meterId,
            'command' => $command,
        ]);
    }
}
