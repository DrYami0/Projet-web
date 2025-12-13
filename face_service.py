from flask import Flask, request, jsonify
import os
import base64
import cv2
import numpy as np
import json

# Import the FaceRecognitionLogin class from the existing script
from face_recognition_login import FaceRecognitionLogin, cosine_similarity, SIMILARITY_THRESHOLD
import pickle
from face_recognition_login import fernet

app = Flask(__name__)

# Load system once
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': '2a10_projet'
}
fr_system = FaceRecognitionLogin(db_config)
# Tuning for authentication safety
# Minimum similarity required for any acceptance and required margin over second-best
MIN_SIM = float(os.environ.get('FACE_MIN_SIM', SIMILARITY_THRESHOLD))
MARGIN = float(os.environ.get('FACE_MARGIN', 0.04))

@app.route('/recognize', methods=['POST'])
def recognize():
    # Debug: show received form keys
    try:
        print(json.dumps({'debug': 'request_keys', 'form_keys': list(request.form.keys()), 'content_length': request.content_length}))
    except Exception:
        pass
    data = request.form or request.json or {}
    img_b64 = data.get('image') if data else None
    img_path = data.get('path') if data else None
    img_bgr = None
    if img_path:
        # allow reading local tmp file path (easier for large uploads from PHP)
        if not os.path.exists(img_path):
            return jsonify({'success': False, 'error': 'Path not found'})
        img_bgr = cv2.imread(img_path)
        if img_bgr is None:
            return jsonify({'success': False, 'error': 'Could not read image from path'})
    elif img_b64:
        try:
            img_bytes = base64.b64decode(img_b64)
            arr = np.frombuffer(img_bytes, dtype=np.uint8)
            img_bgr = cv2.imdecode(arr, cv2.IMREAD_COLOR)
            if img_bgr is None:
                return jsonify({'success': False, 'error': 'Could not decode image'})
        except Exception as e:
            return jsonify({'success': False, 'error': 'Invalid image', 'debug': str(e)})
    else:
        return jsonify({'success': False, 'error': 'No image provided'})
    h, w, c = img_bgr.shape

    image_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
    embedding = fr_system.image_to_embedding(image_rgb)
    if embedding is None:
        return jsonify({'success': False, 'error': 'No face detected'})
    if len(fr_system.known_embeddings) == 0:
        return jsonify({'success': False, 'error': 'No known faces'})
    # Compute similarities robustly and log them for debugging
    # Compute per-embedding similarities, then aggregate by user to avoid
    # false ambiguity when multiple embeddings belong to the same user.
    sim_entries = []
    for idx, e in enumerate(fr_system.known_embeddings):
        try:
            sim = float(cosine_similarity(np.array(e), np.array(embedding)))
        except Exception:
            sim = float('-inf')
        uid = fr_system.known_ids[idx] if idx < len(fr_system.known_ids) else None
        uname = fr_system.known_names[idx] if idx < len(fr_system.known_names) else None
        sim_entries.append({'idx': idx, 'user_id': uid, 'username': uname, 'sim': sim})

    # Best similarity per user_id
    best_per_user = {}
    for s in sim_entries:
        uid = s['user_id']
        if uid is None:
            continue
        if uid not in best_per_user or s['sim'] > best_per_user[uid]['sim']:
            best_per_user[uid] = s

    # Rank users by their best similarity
    ranked = sorted(best_per_user.values(), key=lambda x: x['sim'], reverse=True)

    # Log debug info (include both raw sims and grouped results)
    try:
        debug_log = {
            'known_count': len(fr_system.known_embeddings),
            'known_names': fr_system.known_names,
            'raw_sims_len': len(sim_entries),
            'grouped_count': len(ranked),
            'grouped': [{ 'user_id': r['user_id'], 'username': r['username'], 'sim': r['sim'] } for r in ranked],
            'threshold': float(SIMILARITY_THRESHOLD)
        }
        with open('tmp/face_service_debug.txt', 'a', encoding='utf-8') as fh:
            fh.write(json.dumps(debug_log) + "\n")
    except Exception:
        pass

    if len(ranked) == 0:
        return jsonify({'success': False, 'error': 'No known faces'})

    best_entry = ranked[0]
    best_sim = float(best_entry['sim'])
    user_id = best_entry['user_id']
    username = best_entry.get('username')
    second_sim = float(ranked[1]['sim']) if len(ranked) > 1 else float('-inf')

    # Safety gates: require high minimum similarity and margin versus second-best user
    if best_sim < MIN_SIM:
        return jsonify({'success': False, 'error': 'No confident match (below MIN_SIM)', 'best_sim': best_sim})
    if (best_sim - second_sim) < MARGIN:
        return jsonify({'success': False, 'error': 'Ambiguous match (insufficient margin)', 'best_sim': best_sim, 'second_sim': second_sim})
    try:
        conn = fr_system.connect_db()
        cur = conn.cursor(dictionary=True)
        # try by uid first, then by username
        cur.execute('SELECT uid, username, face_recognition_enabled, status FROM users WHERE uid = %s LIMIT 1', (user_id,))
        row = cur.fetchone()
        if not row and username:
            cur.execute('SELECT uid, username, face_recognition_enabled, status FROM users WHERE username = %s LIMIT 1', (username,))
            row = cur.fetchone()
        cur.close()
        conn.close()
    except Exception:
        row = None

    if not row:
        return jsonify({'success': False, 'error': 'Matched user not found in DB', 'username': username, 'user_id': user_id})
    if int(row.get('face_recognition_enabled') or 0) != 1:
        return jsonify({'success': False, 'error': 'Face login not enabled for this user', 'username': row.get('username')})
    if (row.get('status') or '').lower() != 'active':
        return jsonify({'success': False, 'error': 'Account not active', 'username': row.get('username')})

    return jsonify({'success': True, 'user_id': str(row.get('uid')), 'username': row.get('username'), 'confidence': float(best_sim)})
    return jsonify({'success': False, 'error': 'Face not recognized', 'best_sim': best_sim})


@app.route('/register', methods=['POST'])
def register():
    data = request.form or request.json or {}
    # Log incoming request for debugging
    try:
        dbg = {'request_keys': list(request.form.keys()), 'content_length': request.content_length}
        with open('tmp/face_service_debug.txt', 'a', encoding='utf-8') as fh:
            fh.write(json.dumps({'debug': 'register_request', **dbg}) + "\n")
    except Exception:
        pass
    img_b64 = data.get('image') if data else None
    img_path = data.get('path') if data else None
    user_id = data.get('user_id') or data.get('uid') or data.get('username')
    username = data.get('username') or None

    if not user_id and not username:
        return jsonify({'success': False, 'error': 'Missing user identifier (user_id or username)'}), 400

    img_bgr = None
    if img_path:
        try:
            if not os.path.exists(img_path):
                with open('tmp/face_service_debug.txt', 'a', encoding='utf-8') as fh:
                    fh.write(json.dumps({'debug': 'register_path_missing', 'path': img_path}) + "\n")
                return jsonify({'success': False, 'error': 'Path not found'}), 400
            size = os.path.getsize(img_path)
            with open('tmp/face_service_debug.txt', 'a', encoding='utf-8') as fh:
                fh.write(json.dumps({'debug': 'register_path_exists', 'path': img_path, 'size': size}) + "\n")
            img_bgr = cv2.imread(img_path)
            if img_bgr is None:
                with open('tmp/face_service_debug.txt', 'a', encoding='utf-8') as fh:
                    fh.write(json.dumps({'debug': 'register_cv_read_failed', 'path': img_path}) + "\n")
                return jsonify({'success': False, 'error': 'Could not read image from path'}), 400
        except Exception as e:
            try:
                with open('tmp/face_service_debug.txt', 'a', encoding='utf-8') as fh:
                    fh.write(json.dumps({'debug': 'register_path_exception', 'error': str(e), 'path': img_path}) + "\n")
            except Exception:
                pass
            return jsonify({'success': False, 'error': 'Could not read image from path', 'debug': str(e)}), 400
    elif img_b64:
        try:
            img_bytes = base64.b64decode(img_b64)
            arr = np.frombuffer(img_bytes, dtype=np.uint8)
            img_bgr = cv2.imdecode(arr, cv2.IMREAD_COLOR)
            if img_bgr is None:
                return jsonify({'success': False, 'error': 'Could not decode image'}), 400
        except Exception as e:
            return jsonify({'success': False, 'error': 'Invalid image', 'debug': str(e)}), 400
    else:
        return jsonify({'success': False, 'error': 'No image provided'}), 400

    image_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
    embedding = fr_system.image_to_embedding(image_rgb)
    if embedding is None:
        return jsonify({'success': False, 'error': 'No face detected'}), 400

    # Register in file-backed store (keeps existing behavior)
    try:
        fr_system.register_face(user_id, username or str(user_id), image_path=img_path)
    except Exception as e:
        # continue — we'll still attempt to save to DB
        print(json.dumps({'debug': 'register_file_save_failed', 'error': str(e)}))

    # Persist encrypted embedding into DB per-user
    try:
        conn = fr_system.connect_db()
        cur = conn.cursor()
        # pickle and encrypt embedding
        pickled = pickle.dumps(embedding.tolist())
        enc = fernet.encrypt(pickled)
        # Insert
        cur.execute("INSERT INTO user_face_embeddings (user_uid, embedding, method) VALUES (%s, %s, %s)", (int(user_id) if str(user_id).isdigit() else None, enc, 'mediapipe'))
        conn.commit()
        cur.close()
        conn.close()
    except Exception as e:
        print(json.dumps({'debug': 'db_save_failed', 'error': str(e)}))
        return jsonify({'success': False, 'error': 'Failed to save embedding to DB', 'debug': str(e)}), 500

    return jsonify({'success': True, 'message': 'Face registered', 'user_id': user_id})

if __name__ == '__main__':
    # Run on localhost; start this process under your user account to match CLI behaviour
    app.run(host='127.0.0.1', port=5000)
