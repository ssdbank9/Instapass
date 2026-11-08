import json, urllib.request
req=urllib.request.Request('http://127.0.0.1:8081/',headers={'Host':'instapass.online','X-Forwarded-Proto':'https','X-Forwarded-For':'81.2.69.142'})
with urllib.request.urlopen(req,timeout=20) as response:
    html=response.read().decode('utf-8')
    result={'source':'loopback HTTP request simulating a UK client IP; not physical UK-origin browser traffic','status':response.status,'gbp_default':'Prices in GBP' in html,'usd_switch':'ip_currency=USD' in html,'unpriced_gbp_message':'GBP price coming soon' in html,'cache_control':response.headers.get('Cache-Control')}
    assert result['gbp_default'] and result['usd_switch'], 'UK default or USD switch missing'
    print(json.dumps(result))
