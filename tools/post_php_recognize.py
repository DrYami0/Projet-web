import sys, os, json
url = 'http://127.0.0.1/projet-web/PerFranMVC/Controller/face_recognition_login.php'
path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'tmp', 'face_input.png'))
# Try requests
try:
    import requests
    r = requests.post(url, data={'image': path}, timeout=10)
    print(r.text)
    with open(os.path.join(os.path.dirname(__file__), '..', 'tmp', 'php_recog_resp.txt'), 'w', encoding='utf-8') as fh:
        fh.write(r.text)
    sys.exit(0)
except Exception:
    pass
# Fallback
try:
    from urllib import request, parse
    data = parse.urlencode({'image': path}).encode()
    req = request.Request(url, data=data)
    with request.urlopen(req, timeout=10) as resp:
        body = resp.read().decode('utf-8', errors='replace')
        print(body)
        with open(os.path.join(os.path.dirname(__file__), '..', 'tmp', 'php_recog_resp.txt'), 'w', encoding='utf-8') as fh:
            fh.write(body)
        sys.exit(0)
except Exception as e:
    print(json.dumps({'error': str(e)}))
    sys.exit(1)
