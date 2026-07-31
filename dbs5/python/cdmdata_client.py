"""Minimal CDM Data API client. Stdlib only, no pip install needed."""

import json
import urllib.error
import urllib.parse
import urllib.request


class CdmDataError(Exception):
    def __init__(self, message, status_code=None, body=None):
        super().__init__(message)
        self.status_code = status_code
        self.body = body


class CdmDataClient:
    def __init__(self, access_key, secret_key, base_url="https://dbs5.cplservice.com"):
        self.access_key = access_key
        self.secret_key = secret_key
        self.base_url = base_url.rstrip("/")

    def _request(self, method, path, params=None, json_body=None):
        url = self.base_url + path
        if params:
            query = {k: v for k, v in params.items() if v is not None}
            if query:
                url += "?" + urllib.parse.urlencode(query)

        headers = {
            # Cloudflare (ที่หน้า origin server) บล็อก default UA ของ urllib
            # ("Python-urllib/x.y") ทิ้งแบบ bot จึงต้องตั้ง UA เป็นค่าปกติเอง
            "User-Agent": "cdmdata-http-sdk-python/1.0",
            "x-api-access-key": self.access_key,
            "x-api-secret-key": self.secret_key,
        }
        data = None
        if json_body is not None:
            data = json.dumps(json_body).encode("utf-8")
            headers["content-type"] = "application/json"

        req = urllib.request.Request(url, data=data, headers=headers, method=method)
        try:
            with urllib.request.urlopen(req) as resp:
                return json.loads(resp.read().decode("utf-8"))
        except urllib.error.HTTPError as e:
            body = e.read().decode("utf-8")
            try:
                body = json.loads(body)
            except ValueError:
                pass
            raise CdmDataError(f"HTTP {e.code}", status_code=e.code, body=body) from e

    def get_data(self, device_id, **params):
        return self._request("GET", "/cdmdata/v1/getdata", {"device_id": device_id, **params})

    def get_data_hourly(self, device_id, **params):
        return self._request("GET", "/cdmdata/v1/getdatahourly", {"device_id": device_id, **params})

    def get_data_daily(self, device_id, **params):
        return self._request("GET", "/cdmdata/v1/getdatadaily", {"device_id": device_id, **params})

    def on_off_command(self, device_id, meter_id, command):
        if command not in ("on", "off"):
            raise ValueError("command must be 'on' or 'off'")
        return self._request(
            "POST",
            "/cdmdata/v1/on-off-command",
            json_body={"device_id": device_id, "meter_id": meter_id, "command": command},
        )


if __name__ == "__main__":
    # ponytail: self-check for URL/header building only, no real network call
    from unittest import mock

    captured = {}

    class _FakeResponse:
        def __enter__(self):
            return self

        def __exit__(self, *exc):
            return False

        def read(self):
            return b'{"status": true, "body": {}}'

    def _fake_urlopen(req, *a, **kw):
        captured["url"] = req.full_url
        captured["headers"] = req.headers
        return _FakeResponse()

    client = CdmDataClient("AK_TEST", "SK_TEST")
    with mock.patch("urllib.request.urlopen", _fake_urlopen):
        client.get_data_hourly("abc123", limit=1, transform=1)

    assert "device_id=abc123" in captured["url"]
    assert "limit=1" in captured["url"]
    assert captured["headers"]["X-api-access-key"] == "AK_TEST"
    print("cdmdata_client.py self-check OK")
