'use strict';

// Minimal CDM Data API client. Uses global fetch (Node.js >=18), no npm install needed.

const DEFAULT_BASE_URL = 'https://dbs5.cplservice.com';

class CdmDataError extends Error {
  constructor(message, statusCode, body) {
    super(message);
    this.statusCode = statusCode;
    this.body = body;
  }
}

class CdmDataClient {
  constructor(accessKey, secretKey, baseUrl = DEFAULT_BASE_URL) {
    this.accessKey = accessKey;
    this.secretKey = secretKey;
    this.baseUrl = baseUrl.replace(/\/$/, '');
  }

  async _request(method, path, { params, jsonBody } = {}) {
    const url = new URL(this.baseUrl + path);
    if (params) {
      for (const [k, v] of Object.entries(params)) {
        if (v !== undefined && v !== null) url.searchParams.set(k, v);
      }
    }

    const headers = {
      'x-api-access-key': this.accessKey,
      'x-api-secret-key': this.secretKey,
    };
    let body;
    if (jsonBody !== undefined) {
      headers['content-type'] = 'application/json';
      body = JSON.stringify(jsonBody);
    }

    const res = await fetch(url, { method, headers, body });
    const text = await res.text();
    const data = text ? JSON.parse(text) : null;
    if (!res.ok) {
      throw new CdmDataError(`HTTP ${res.status}`, res.status, data);
    }
    return data;
  }

  getData(deviceId, params = {}) {
    return this._request('GET', '/cdmdata/v1/getdata', { params: { device_id: deviceId, ...params } });
  }

  getDataHourly(deviceId, params = {}) {
    return this._request('GET', '/cdmdata/v1/getdatahourly', { params: { device_id: deviceId, ...params } });
  }

  getDataDaily(deviceId, params = {}) {
    return this._request('GET', '/cdmdata/v1/getdatadaily', { params: { device_id: deviceId, ...params } });
  }

  onOffCommand(deviceId, meterId, command) {
    if (command !== 'on' && command !== 'off') {
      throw new Error("command must be 'on' or 'off'");
    }
    return this._request('POST', '/cdmdata/v1/on-off-command', {
      jsonBody: { device_id: deviceId, meter_id: meterId, command },
    });
  }
}

module.exports = { CdmDataClient, CdmDataError };

if (require.main === module) {
  // ponytail: self-check for URL/header building only, no real network call
  const assert = require('node:assert');
  const client = new CdmDataClient('AK_TEST', 'SK_TEST');
  const originalFetch = global.fetch;
  global.fetch = async (url, opts) => {
    assert.ok(url.toString().includes('device_id=abc123'), 'device_id missing from URL');
    assert.ok(url.toString().includes('limit=1'), 'limit missing from URL');
    assert.strictEqual(opts.headers['x-api-access-key'], 'AK_TEST');
    return { ok: true, status: 200, text: async () => JSON.stringify({ status: true, body: {} }) };
  };
  client
    .getDataHourly('abc123', { limit: 1, transform: 1 })
    .then(() => {
      global.fetch = originalFetch;
      console.log('cdmdata-client.js self-check OK');
    })
    .catch((err) => {
      global.fetch = originalFetch;
      console.error('self-check FAILED:', err);
      process.exit(1);
    });
}
