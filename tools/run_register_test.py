import sys
import os
import pickle
import numpy as np
# ensure project root is importable
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
from face_recognition_login import FaceRecognitionLogin, fernet
import cv2

if len(sys.argv) < 4:
    print('Usage: python tools/run_register_test.py <image_path> <user_id> <username>')
    sys.exit(1)

image_path = sys.argv[1]
user_id = sys.argv[2]
username = sys.argv[3]

if not os.path.exists(image_path):
    print({'success': False, 'error': 'Path not found', 'path': image_path})
    sys.exit(1)

img_bgr = cv2.imread(image_path)
if img_bgr is None:
    print({'success': False, 'error': 'Could not read image from path', 'path': image_path})
    sys.exit(1)

image_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)

# DB config same as face_service
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': '2a10_projet'
}

fr_system = FaceRecognitionLogin(db_config)
embedding = fr_system.image_to_embedding(image_rgb)
if embedding is None:
    print({'success': False, 'error': 'No face detected'})
    sys.exit(1)

try:
    conn = fr_system.connect_db()
    cur = conn.cursor()
    pickled = pickle.dumps(embedding.tolist())
    enc = fernet.encrypt(pickled)
    cur.execute("INSERT INTO user_face_embeddings (user_uid, embedding, method) VALUES (%s, %s, %s)", (int(user_id) if str(user_id).isdigit() else None, enc, 'mediapipe'))
    conn.commit()
    cur.close()
    conn.close()
    print({'success': True, 'message': 'Face registered', 'user_id': user_id})
except Exception as e:
    print({'success': False, 'error': 'DB save failed', 'debug': str(e)})
    sys.exit(1)
