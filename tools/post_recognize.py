import sys
import json
import os
url = 'http://127.0.0.1:5000/recognize'
path = os.path.join(os.path.dirname(__file__), '..', 'tmp', 'face_input.png')
path = os.path.abspath(path)
# Try requests first
try:
    import requests
    r = requests.post(url, data={'path': path}, timeout=10)
    outp = r.text if hasattr(r, 'text') else str(r.content)
    # persist response to tmp for reliable inspection
    with open(os.path.join(os.path.dirname(__file__), '..', 'tmp', 'face_service_test_resp.txt'), 'w', encoding='utf-8') as fh:
        fh.write(outp)
    print(outp)
    sys.exit(0 if r.status_code==200 else 1)
except Exception:
    pass
# Fallback to urllib
try:
    from urllib import request, parse
    data = parse.urlencode({'path': path}).encode()
    req = request.Request(url, data=data)
    with request.urlopen(req, timeout=10) as resp:
        body = resp.read().decode('utf-8', errors='replace')
        # persist response to tmp for reliable inspection
        with open(os.path.join(os.path.dirname(__file__), '..', 'tmp', 'face_service_test_resp.txt'), 'w', encoding='utf-8') as fh:
            fh.write(body)
        print(body)
        sys.exit(0)
except Exception as e:
    print(json.dumps({'error': 'failed', 'exception': str(e)}))
    sys.exit(2)
