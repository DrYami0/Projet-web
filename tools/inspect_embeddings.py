import os, pickle, json
from cryptography.fernet import Fernet
pkey = os.path.join(os.path.dirname(__file__), '..', 'face_key.txt')
efile = os.path.join(os.path.dirname(__file__), '..', 'face_data', 'face_embeddings.pkl')

keys_to_try = []
try:
    keys_to_try.append(open(pkey,'rb').read().strip())
except Exception:
    pass
# also try keys observed in logs (fallback candidates)
keys_to_try.extend([b'REDACTED_FACE_ENCRYPT_KEY=', b'c9Xv2IKVCR_hhzpRU-TgFJMq8rpwwa6m4KAxFNQrgWk='])

data = None
try:
    data = open(efile,'rb').read()
except Exception as e:
    print(json.dumps({'error':'missing_embeddings_file','exc':str(e)}))
    raise

for key in keys_to_try:
    try:
        f = Fernet(key)
        dec = f.decrypt(data)
        obj = pickle.loads(dec)
        embs = obj.get('embeddings', [])
        ids = obj.get('ids', [])
        names = obj.get('names', [])
        out = {'key_used': key.decode() if isinstance(key, (bytes,bytearray)) else str(key), 'count': len(embs), 'ids_len': len(ids), 'names_len': len(names)}
        out['first_shapes'] = [len(e) if hasattr(e,'__len__') else None for e in embs[:5]]
        print(json.dumps(out))
        break
    except Exception as e:
        print(json.dumps({'key_try': key.decode() if isinstance(key, (bytes,bytearray)) else str(key), 'error': str(e)}))
