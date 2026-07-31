'use strict';

// Minimal CDM Data API client (legacy / dbs7, cust_id-based). Uses global fetch (Node.js >=18), no npm install needed.

const DEFAULT_BASE_URL = 'https://dbs7.cplservice.com';

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

  // ไฟฟ้า
  getData(custId, params = {}) {
    return this._request('GET', '/cdmdata/v1/getdata', { params: { cust_id: custId, ...params } });
  }

  getElectricHourly(custId, params = {}) {
    return this._request('GET', '/cdmdata/v1/electric1hour', { params: { cust_id: custId, ...params } });
  }

  getDataDaily(custId, params = {}) {
    return this._request('GET', '/cdmdata/v1/getdatadaily', { params: { cust_id: custId, ...params } });
  }

  // น้ำ
  getWater(custId, params = {}) {
    return this._request('GET', '/cdmdata/v1/getdata_water', { params: { cust_id: custId, ...params } });
  }

  getWaterHourly(custId, params = {}) {
    return this._request('GET', '/cdmdata/v1/getdata1hour_water', { params: { cust_id: custId, ...params } });
  }

  getWaterDaily(custId, params = {}) {
    return this._request('GET', '/cdmdata/v1/getdatadaily_water', { params: { cust_id: custId, ...params } });
  }

  // on/off (remote-cutoff)
  getOnOffStatus(custId, params = {}) {
    return this._request('GET', '/cdmdata/v1/on-off-command', { params: { cust_id: custId, ...params } });
  }

  onOffCommand(custId, command, { meterAddress, meterId } = {}) {
    if (command !== 'on' && command !== 'off') {
      throw new Error("command must be 'on' or 'off'");
    }
    const jsonBody = { cust_id: custId, command };
    if (meterAddress !== undefined) jsonBody.meter_address = meterAddress;
    if (meterId !== undefined) jsonBody.meter_id = meterId;
    return this._request('POST', '/cdmdata/v1/on-off-command', { jsonBody });
  }
}

module.exports = { CdmDataClient, CdmDataError };

if (require.main === module) {
  // ponytail: self-check for URL/header building only, no real network call
  const assert = require('node:assert');
  const client = new CdmDataClient('AK_TEST', 'SK_TEST');
  const originalFetch = global.fetch;
  global.fetch = async (url, opts) => {
    assert.ok(url.toString().includes('cust_id=270000005'), 'cust_id missing from URL');
    assert.ok(url.toString().includes('limit=1'), 'limit missing from URL');
    assert.strictEqual(opts.headers['x-api-access-key'], 'AK_TEST');
    return { ok: true, status: 200, text: async () => JSON.stringify({ status: true, body: {} }) };
  };
  client
    .getDataDaily('270000005', { limit: 1 })
    .then(() => {
      global.fetch = originalFetch;
      console.log('cdmdata-client.js (dbs7 legacy) self-check OK');
    })
    .catch((err) => {
      global.fetch = originalFetch;
      console.error('self-check FAILED:', err);
      process.exit(1);
    });
}
