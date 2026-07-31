"""Minimal CDM Data API client (legacy / dbs7, cust_id-based). Stdlib only, no pip install needed."""

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
    def __init__(self, access_key, secret_key, base_url="https://dbs7.cplservice.com"):
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

    # ไฟฟ้า
    def get_data(self, cust_id, **params):
        return self._request("GET", "/cdmdata/v1/getdata", {"cust_id": cust_id, **params})

    def get_electric_hourly(self, cust_id, **params):
        return self._request("GET", "/cdmdata/v1/electric1hour", {"cust_id": cust_id, **params})

    def get_data_daily(self, cust_id, **params):
        return self._request("GET", "/cdmdata/v1/getdatadaily", {"cust_id": cust_id, **params})

    # น้ำ
    def get_water(self, cust_id, **params):
        return self._request("GET", "/cdmdata/v1/getdata_water", {"cust_id": cust_id, **params})

    def get_water_hourly(self, cust_id, **params):
        return self._request("GET", "/cdmdata/v1/getdata1hour_water", {"cust_id": cust_id, **params})

    def get_water_daily(self, cust_id, **params):
        return self._request("GET", "/cdmdata/v1/getdatadaily_water", {"cust_id": cust_id, **params})

    # on/off (remote-cutoff)
    def get_on_off_status(self, cust_id, **params):
        return self._request("GET", "/cdmdata/v1/on-off-command", {"cust_id": cust_id, **params})

    def on_off_command(self, cust_id, command, meter_address=None, meter_id=None):
        if command not in ("on", "off"):
            raise ValueError("command must be 'on' or 'off'")
        body = {"cust_id": cust_id, "command": command}
        if meter_address is not None:
            body["meter_address"] = meter_address
        if meter_id is not None:
            body["meter_id"] = meter_id
        return self._request("POST", "/cdmdata/v1/on-off-command", json_body=body)


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
        client.get_data_daily("270000005", limit=1)

    assert "cust_id=270000005" in captured["url"]
    assert "limit=1" in captured["url"]
    assert captured["headers"]["X-api-access-key"] == "AK_TEST"
    print("cdmdata_client.py (dbs7 legacy) self-check OK")
