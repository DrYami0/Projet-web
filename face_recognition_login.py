print("LOADED ROOT VERSION: face_recognition_login.py at", __file__)
ZZZ_BREAK_HERE = True
import cv2
import mysql.connector
import numpy as np
import os
import pickle
from datetime import datetime
import json
import argparse
from cryptography.fernet import Fernet
import base64
import sys
try:
    from dotenv import load_dotenv
    load_dotenv()
except Exception:
    pass

# Very early execution diagnostics (helps confirm which file Apache runs)
try:
    script_file = os.path.abspath(__file__)
    print("[DEBUG] __file__ =", script_file)
    print("[DEBUG] cwd =", os.getcwd())
    try:
        print("[DEBUG] cwd listing =", os.listdir('.'))
    except Exception as _le:
        print("[DEBUG] cwd list failed:", _le)
    script_dir = os.path.dirname(script_file)
    print("[DEBUG] script_dir =", script_dir)
    models_dir = os.path.join(script_dir, 'models')
    print("[DEBUG] models_dir =", models_dir)
    try:
        print("[DEBUG] models listing =", os.listdir(models_dir))
    except Exception as _me:
        print("[DEBUG] models list failed:", _me)
except Exception as _e:
    print("[DEBUG] early_exec_failed:", _e)

# Apache / webserver runtime debug helpers
import sys, os
print("=== DEBUG APACHE ENVIRONMENT ===")
print("Python executable:", sys.executable)
print("Python version:", sys.version)
print("CWD:", os.getcwd())
print("USER:", os.getenv("USER") or os.getenv("USERNAME"))
print("PATH:", os.getenv("PATH"))
print("VIRTUAL_ENV:", os.getenv("VIRTUAL_ENV"))
print("LD_LIBRARY_PATH:", os.getenv("LD_LIBRARY_PATH"))
print("PYTHONPATH:", os.getenv("PYTHONPATH"))
print("FACE_MIN_DET_CONF:", os.getenv("FACE_MIN_DET_CONF"))
print("TMPDIR:", os.getenv("TMPDIR") or os.getenv("TEMP") or os.getenv('TMP'))
try:
    import mediapipe as _mp
    mp_ver = getattr(_mp, '__version__', 'unknown')
except Exception as _e:
    mp_ver = f"import_error: {_e}"
print("MEDIAPIPE VERSION:", mp_ver)
print("================================")

try:
    print(json.dumps({'debug': 'executed_file', 'path': os.path.abspath(__file__)}))
    print(json.dumps({'debug': 'sys_path', 'sys_path': sys.path}))
    try:
        models_dir = os.path.join(os.path.dirname(__file__), 'models')
        print(json.dumps({'debug': 'models_listing', 'files': os.listdir(models_dir)}))
    except Exception as _me:
        print(json.dumps({'debug': 'models_listing_error', 'error': str(_me)}))
except Exception as _e:
    print(json.dumps({'debug': 'executed_file_error', 'error': str(_e)}))


def debug_file(path):
    try:
        exists = os.path.exists(path)
        print("Uploaded file exists =", exists)
        size = os.path.getsize(path) if exists else "N/A"
        print("File size =", size)
    except Exception as e:
        print("debug_file error:", e)

# Headless mode setup
if any(a.startswith('--mode') for a in sys.argv) or os.environ.get('API_FACE_HEADLESS'):
    mplconf = os.path.join(os.path.dirname(__file__), 'mplconfig')
    os.environ.setdefault('MPLCONFIGDIR', mplconf)
    if not os.environ.get('HOME') and os.environ.get('USERPROFILE'):
        os.environ['HOME'] = os.environ['USERPROFILE']
    os.environ.setdefault('SDL_VIDEODRIVER', 'dummy')

def safe_print(*args, **kwargs):
    if not any(a.startswith('--mode') for a in sys.argv):
        print(*args, **kwargs)

# Configuration
EMBEDDINGS_FILE = os.path.join(os.path.dirname(__file__), "face_data", "face_embeddings.pkl")

try:
    SIMILARITY_THRESHOLD = float(os.environ.get('FACE_SIMILARITY_THRESHOLD', '0.65'))
except Exception:
    SIMILARITY_THRESHOLD = 0.65

mp_face_mesh = None

def l2_normalize(vec):
    norm = np.linalg.norm(vec)
    if norm == 0:
        return vec
    return vec / norm

def compute_embedding_from_landmarks(landmarks):
    lm = np.array(landmarks)
    xy = lm[:, :2]
    center = xy.mean(axis=0)
    centered = xy - center
    dists = np.linalg.norm(centered, axis=1)
    scale = dists.max() if dists.max() > 1e-6 else 1.0
    scaled = centered / scale
    z = lm[:, 2:3] / scale
    vec = np.concatenate([scaled, z], axis=1).flatten()
    return l2_normalize(vec)

def preprocess_image_clahe(image_rgb):
    """Enhanced preprocessing for webcam images with color casts and compression artifacts"""
    try:
        image_bgr = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2BGR)
        lab = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2LAB)
        l, a, b = cv2.split(lab)
        
        # More aggressive CLAHE for better contrast
        clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
        l_enhanced = clahe.apply(l)
        
        # Normalize A and B channels to reduce color cast (pink/purple tint)
        a_normalized = cv2.normalize(a, None, 0, 255, cv2.NORM_MINMAX)
        b_normalized = cv2.normalize(b, None, 0, 255, cv2.NORM_MINMAX)
        
        merged = cv2.merge((l_enhanced, a_normalized, b_normalized))
        enhanced_bgr = cv2.cvtColor(merged, cv2.COLOR_LAB2BGR)
        
        # Apply sharpening to enhance facial features
        kernel = np.array([[0, -1, 0],
                          [-1, 5, -1],
                          [0, -1, 0]])
        sharpened = cv2.filter2D(enhanced_bgr, -1, kernel)
        
        enhanced_rgb = cv2.cvtColor(sharpened, cv2.COLOR_BGR2RGB)
        return enhanced_rgb
    except Exception as e:
        print(json.dumps({'debug': 'clahe_exception', 'error': str(e)}))
        return image_rgb


def detect_face_opencv_fallback(image_bgr):
    """Fallback detector when MediaPipe fails. Returns face RGB crop or None."""
    try:
        gray = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2GRAY)
        cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")

        faces = cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5)

        if len(faces) == 0:
            print(json.dumps({'debug': 'fallback_no_face'}))
            return None

        print(json.dumps({'debug': 'fallback_face_detected'}))
        (x, y, w, h) = faces[0]
        # Expand box slightly for better context
        pad_x = int(w * 0.12)
        pad_y = int(h * 0.18)
        x0 = max(0, x - pad_x)
        y0 = max(0, y - pad_y)
        x1 = min(image_bgr.shape[1], x + w + pad_x)
        y1 = min(image_bgr.shape[0], y + h + pad_y)
        face = image_bgr[y0:y1, x0:x1]
        face_rgb = cv2.cvtColor(face, cv2.COLOR_BGR2RGB)
        return face_rgb

    except Exception as e:
        print(json.dumps({'debug': 'fallback_error', 'error': str(e)}))
        return None


def fallback_embedding_from_face(face_rgb):
    """Create a deterministic numeric embedding from a face crop using HSV histogram.
    This is a lightweight fallback so the system remains operational when MediaPipe
    is unavailable. The returned vector is L2-normalized.
    """
    try:
        face_hsv = cv2.cvtColor(face_rgb, cv2.COLOR_RGB2HSV)
        # 8x8x8 histogram -> 512 dims
        hist = cv2.calcHist([face_hsv], [0, 1, 2], None, [8, 8, 8], [0, 180, 0, 256, 0, 256])
        hist = cv2.normalize(hist, hist).flatten()
        vec = np.asarray(hist, dtype=np.float32)
        norm = np.linalg.norm(vec)
        if norm == 0:
            return vec
        return vec / norm
    except Exception as e:
        print(json.dumps({'debug': 'fallback_embedding_error', 'error': str(e)}))
        return None


# --- OpenCV DNN face detector (res10_300x300_ssd) ---
dnn_net = None
try:
    # Use absolute paths relative to the script location so PHP CWD doesn't break model loading
    # script_dir is set in the early diagnostics above
    dnn_proto = os.path.join(script_dir, 'models', 'deploy.prototxt')
    dnn_model = os.path.join(script_dir, 'models', 'res10_300x300_ssd_iter_140000.caffemodel')
    # Explicit debug: show absolute paths and existence so Apache logs confirm files
    try:
        abs_proto = os.path.abspath(dnn_proto)
        abs_model = os.path.abspath(dnn_model)
        print(json.dumps({'debug': 'dnn_paths', 'proto': abs_proto, 'model': abs_model}))
        print(json.dumps({'debug': 'dnn_exists', 'proto_exists': os.path.exists(abs_proto), 'model_exists': os.path.exists(abs_model)}))
        if os.path.exists(abs_proto):
            try:
                print(json.dumps({'debug': 'proto_size', 'bytes': os.path.getsize(abs_proto)}))
            except Exception:
                pass
        if os.path.exists(abs_model):
            try:
                print(json.dumps({'debug': 'model_size', 'bytes': os.path.getsize(abs_model)}))
            except Exception:
                pass
    except Exception as _e:
        print(json.dumps({'debug': 'dnn_path_debug_error', 'error': str(_e)}))
    try:
        dnn_net = cv2.dnn.readNetFromCaffe(dnn_proto, dnn_model)
        print("[DNN] Loaded model successfully")
    except Exception as e:
        dnn_net = None
        print("[DNN] Failed to load model:", e)
except Exception as e:
    dnn_net = None
    print("[DNN] DNN init exception:", e)


def detect_face_dnn(image_bgr, conf_threshold=0.4):
    """More robust fallback using OpenCV DNN SSD face detector."""
    global dnn_net
    if dnn_net is None:
        print(json.dumps({'debug': 'dnn_unavailable'}))
        return None

    (h, w) = image_bgr.shape[:2]
    blob = cv2.dnn.blobFromImage(cv2.resize(image_bgr, (300, 300)), 1.0, (300, 300), (104.0, 177.0, 123.0))
    dnn_net.setInput(blob)
    detections = dnn_net.forward()

    best_face = None
    best_conf = 0.0
    for i in range(0, detections.shape[2]):
        confidence = float(detections[0, 0, i, 2])
        if confidence > conf_threshold and confidence > best_conf:
            box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
            (x1, y1, x2, y2) = box.astype('int')
            # Clip box
            x1 = max(0, x1); y1 = max(0, y1); x2 = min(w, x2); y2 = min(h, y2)
            if x2 <= x1 or y2 <= y1:
                continue
            best_face = image_bgr[y1:y2, x1:x2]
            best_conf = confidence

    if best_face is None:
        print(json.dumps({'debug': 'dnn_no_face'}))
        return None

    print(json.dumps({'debug': 'dnn_face_detected', 'confidence': float(best_conf)}))
    return cv2.cvtColor(best_face, cv2.COLOR_BGR2RGB)

def cosine_similarity(a, b):
    if a is None or b is None: return -1.0
    a = a.flatten()
    b = b.flatten()
    denom = np.linalg.norm(a) * np.linalg.norm(b)
    if denom == 0: return -1.0
    return float(np.dot(a, b) / denom)

def match_embeddings(stored_emb, new_emb):
    a = np.asarray(stored_emb, dtype=np.float32).flatten()
    b = np.asarray(new_emb, dtype=np.float32).flatten()

    if a.size == 0 or b.size == 0:
        print("Distance =", None)
        return False

    # If dimensions differ (legacy embeddings), attempt a safe comparison
    if a.size != b.size:
        # Use the common prefix to compute a fallback distance instead of outright failing
        mn = min(a.size, b.size)
        a = a[:mn]
        b = b[:mn]

    na = a / np.linalg.norm(a) if np.linalg.norm(a) != 0 else a
    nb = b / np.linalg.norm(b) if np.linalg.norm(b) != 0 else b

    dist = float(np.linalg.norm(na - nb))
    print("Distance =", dist)

    dim = na.size
    if dim == 128:
        threshold = 1.0
    elif dim == 512:
        threshold = 0.6
    else:
        threshold = 0.6

    return dist < threshold

def embedding_distance(stored_emb, new_emb):
    a = np.asarray(stored_emb, dtype=np.float32).flatten()
    b = np.asarray(new_emb, dtype=np.float32).flatten()
    if a.size == 0 or b.size == 0:
        return None
    # If sizes differ, fall back to comparing on the common prefix length
    if a.size != b.size:
        mn = min(a.size, b.size)
        a = a[:mn]
        b = b[:mn]
    na = a / np.linalg.norm(a) if np.linalg.norm(a) != 0 else a
    nb = b / np.linalg.norm(b) if np.linalg.norm(b) != 0 else b
    dist = float(np.linalg.norm(na - nb))
    return dist

# Encryption
# Determine encryption key: prefer env var, then persisted file, otherwise generate and persist.
ENCRYPT_KEY = os.environ.get('FACE_ENCRYPT_KEY')
if not ENCRYPT_KEY:
    try:
        key_path = os.path.join(os.path.dirname(__file__), 'face_key.txt')
        if os.path.exists(key_path):
            with open(key_path, 'rb') as _kf:
                k = _kf.read().strip()
                if k:
                    ENCRYPT_KEY = k
    except Exception:
        ENCRYPT_KEY = None

# Ensure bytes for Fernet
if ENCRYPT_KEY and isinstance(ENCRYPT_KEY, str):
    ENCRYPT_KEY = ENCRYPT_KEY.encode()

if not ENCRYPT_KEY:
    ENCRYPT_KEY = Fernet.generate_key()
    try:
        key_path = os.path.join(os.path.dirname(__file__), 'face_key.txt')
        with open(key_path, 'wb') as _kf:
            _kf.write(ENCRYPT_KEY)
    except Exception:
        pass
    print(f"[SECURITY] Generated and persisted FACE_ENCRYPT_KEY: {ENCRYPT_KEY.decode()}")

fernet = Fernet(ENCRYPT_KEY)

class FaceRecognitionLogin:
    def __init__(self, db_config, embeddings_file=EMBEDDINGS_FILE):
        self.db_config = db_config
        self.embeddings_file = embeddings_file
        self.known_embeddings = []
        self.known_ids = []
        self.known_names = []
        self.load_known_embeddings()

        global mp_face_mesh
        try:
            import mediapipe as mp
            mp_face_mesh = mp.solutions.face_mesh
            # CHANGED: Lower default confidence to 0.1 for better webcam detection
            try:
                min_det = float(os.environ.get('FACE_MIN_DET_CONF', 0.1))
            except Exception:
                min_det = 0.1
            self.mp_face_mesh_static = mp_face_mesh.FaceMesh(static_image_mode=True,
                                                             max_num_faces=1,
                                                             refine_landmarks=True,
                                                             min_detection_confidence=min_det)
            self.mp_face_mesh_stream = mp_face_mesh.FaceMesh(static_image_mode=False,
                                                             max_num_faces=1,
                                                             refine_landmarks=True,
                                                             min_detection_confidence=min_det,
                                                             min_tracking_confidence=0.5)
        except Exception as e:
            safe_print(f"[WARN] mediapipe import or FaceMesh init failed: {e}")
            mp_face_mesh = None
            self.mp_face_mesh_static = None
            self.mp_face_mesh_stream = None

    def connect_db(self):
        return mysql.connector.connect(
            host=self.db_config['host'],
            user=self.db_config['user'],
            password=self.db_config['password'],
            database=self.db_config['database']
        )

    def load_known_embeddings(self):
        # Prefer loading embeddings from the DB. If DB provides any rows, use them
        # and skip the legacy file-backed store to avoid fake/file identities.
        try:
            conn = self.connect_db()
            cursor = conn.cursor(dictionary=True)
            cursor.execute('SELECT id,user_uid,embedding,method,metadata,created_at FROM user_face_embeddings')
            rows = cursor.fetchall()
            added = 0
            for r in rows:
                try:
                    emb_blob = r.get('embedding')
                    if emb_blob is None:
                        continue
                    if isinstance(emb_blob, memoryview):
                        emb_blob = emb_blob.tobytes()
                    if not isinstance(emb_blob, (bytes, bytearray)):
                        continue
                    try:
                        dec = fernet.decrypt(emb_blob)
                        arr = pickle.loads(dec)
                    except Exception:
                        continue
                    emb_np = np.array(arr, dtype=np.float32)
                    self.known_embeddings.append(emb_np)
                    self.known_ids.append(r.get('user_uid'))
                    # lookup username for clarity
                    name = None
                    try:
                        cur2 = conn.cursor(dictionary=True)
                        cur2.execute('SELECT username FROM users WHERE uid = %s LIMIT 1', (r.get('user_uid'),))
                        rowu = cur2.fetchone()
                        if rowu and rowu.get('username'):
                            name = rowu.get('username')
                        cur2.close()
                    except Exception:
                        name = None
                    if not name:
                        name = str(r.get('user_uid'))
                    self.known_names.append(name)
                    added += 1
                except Exception:
                    continue
            cursor.close()
            conn.close()
            if added > 0:
                safe_print(f"[INFO] Loaded {added} embeddings from DB (user_face_embeddings)")
                return
        except Exception as e:
            safe_print(f"[WARN] Could not load embeddings from DB: {e}")

        # Fallback: only if DB had no embeddings, load legacy file-backed store
        if os.path.exists(self.embeddings_file):
            with open(self.embeddings_file, 'rb') as f:
                encrypted = f.read()
                try:
                    data = fernet.decrypt(encrypted)
                    data = pickle.loads(data)
                except Exception as e:
                    safe_print("[ERROR] Failed to decrypt embeddings:", e)
                    data = {'embeddings': [], 'ids': [], 'names': []}
                self.known_embeddings = [np.array(e) for e in data.get('embeddings', [])]
                self.known_ids = data.get('ids', [])
                self.known_names = data.get('names', [])
                safe_print(f"[INFO] Loaded {len(self.known_embeddings)} known face embeddings (encrypted file)")
        else:
            safe_print("[WARN] No embeddings file found. Please register faces first.")

    def save_known_embeddings(self):
        data = {
            'embeddings': [e.tolist() for e in self.known_embeddings],
            'ids': self.known_ids,
            'names': self.known_names
        }
        pickled = pickle.dumps(data)
        encrypted = fernet.encrypt(pickled)
        with open(self.embeddings_file, 'wb') as f:
            f.write(encrypted)
        safe_print(f"[INFO] Saved {len(self.known_embeddings)} face embeddings (encrypted)")

    def image_to_embedding(self, image_rgb):
        """Enhanced image to embedding with aggressive preprocessing"""
        # If MediaPipe is not available, attempt OpenCV fallback detector + fallback embedding
        if not self.mp_face_mesh_static:
            safe_print("[WARN] MediaPipe FaceMesh is not available - using OpenCV fallback")
            try:
                image_bgr = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2BGR)
                face_crop_rgb = detect_face_opencv_fallback(image_bgr)
                if face_crop_rgb is None:
                    safe_print('[INFO] Haar fallback failed, trying DNN fallback')
                    face_crop_rgb = detect_face_dnn(image_bgr, conf_threshold=0.35)
                if face_crop_rgb is None:
                    safe_print('[ERROR] OpenCV fallbacks also failed to detect a face')
                    return None
                emb = fallback_embedding_from_face(face_crop_rgb)
                if emb is None:
                    safe_print('[ERROR] Failed to compute fallback embedding')
                    return None
                return emb
            except Exception as e:
                safe_print(f"[ERROR] OpenCV fallback exception: {e}")
                return None
        
        # Strategy 1: Try with enhanced preprocessing first
        print(json.dumps({'debug': 'trying_enhanced_preprocessing'}))
        img_enhanced = preprocess_image_clahe(image_rgb)
        result = self.mp_face_mesh_static.process(img_enhanced)
        
        # Strategy 2: If failed, try original image
        if not result or not result.multi_face_landmarks:
            print(json.dumps({'debug': 'enhanced_failed_trying_original'}))
            result = self.mp_face_mesh_static.process(image_rgb)
        
        # Strategy 3: Try upscaling with enhanced image
        if not result or not result.multi_face_landmarks:
            h, w, _ = image_rgb.shape
            if min(h, w) < 1200:
                print(json.dumps({'debug': 'trying_upscale'}))
                up = cv2.resize(img_enhanced, (0, 0), fx=2.0, fy=2.0, interpolation=cv2.INTER_CUBIC)
                result = self.mp_face_mesh_static.process(up)
        
        # Strategy 4: Try horizontal flip
        if not result or not result.multi_face_landmarks:
            print(json.dumps({'debug': 'trying_flip'}))
            flip = cv2.flip(img_enhanced, 1)
            result = self.mp_face_mesh_static.process(flip)
        
        # Strategy 5: Last resort - very aggressive enhancement
        if not result or not result.multi_face_landmarks:
            print(json.dumps({'debug': 'trying_aggressive_enhancement'}))
            image_bgr = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2BGR)
            lab = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2LAB)
            l, a, b = cv2.split(lab)
            clahe = cv2.createCLAHE(clipLimit=4.0, tileGridSize=(4, 4))
            l = clahe.apply(l)
            enhanced = cv2.merge((l, a, b))
            enhanced_bgr = cv2.cvtColor(enhanced, cv2.COLOR_LAB2BGR)
            enhanced_rgb = cv2.cvtColor(enhanced_bgr, cv2.COLOR_BGR2RGB)
            result = self.mp_face_mesh_static.process(enhanced_rgb)
        
        if not result or not result.multi_face_landmarks:
            print(json.dumps({'debug': 'all_strategies_failed'}))
            # As a last resort, try OpenCV fallback on the original image
            try:
                image_bgr = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2BGR)
                face_crop_rgb = detect_face_opencv_fallback(image_bgr)
                if face_crop_rgb is None:
                    safe_print('[INFO] Haar failed — trying DNN fallback')
                    face_crop_rgb = detect_face_dnn(image_bgr, conf_threshold=0.35)

                if face_crop_rgb is not None:
                    safe_print('[INFO] MediaPipe failed - used OpenCV fallback for embedding')
                    emb = fallback_embedding_from_face(face_crop_rgb)
                    if emb is not None:
                        return emb
            except Exception as e:
                safe_print(f"[ERROR] Fallback attempt failed: {e}")
            return None
        
        print(json.dumps({'debug': 'face_detected_successfully'}))
        
        face_landmarks = result.multi_face_landmarks[0]
        h, w, _ = image_rgb.shape
        landmarks = []
        for lm in face_landmarks.landmark:
            landmarks.append((lm.x, lm.y, lm.z))

        return compute_embedding_from_landmarks(landmarks)

    def webcam_frame_to_embedding(self, frame_bgr):
        """For streaming mode"""
        image_rgb = cv2.cvtColor(frame_bgr, cv2.COLOR_BGR2RGB)
        if not self.mp_face_mesh_stream:
            safe_print("[ERROR] MediaPipe FaceMesh stream is not available")
            return None, frame_bgr
        
        # Apply enhanced preprocessing for webcam frames too
        img_enhanced = preprocess_image_clahe(image_rgb)
        result = self.mp_face_mesh_stream.process(img_enhanced)
        
        if not result or not result.multi_face_landmarks:
            result = self.mp_face_mesh_stream.process(image_rgb)
            if not result or not result.multi_face_landmarks:
                h, w, _ = image_rgb.shape
                if min(h, w) < 1200:
                    up = cv2.resize(img_enhanced, (0, 0), fx=1.5, fy=1.5, interpolation=cv2.INTER_LINEAR)
                    result = self.mp_face_mesh_stream.process(up)
                if not result or not result.multi_face_landmarks:
                    flip = cv2.flip(img_enhanced, 1)
                    result = self.mp_face_mesh_stream.process(flip)
                if not result or not result.multi_face_landmarks:
                    return None, frame_bgr

        face_landmarks = result.multi_face_landmarks[0]
        h, w, _ = image_rgb.shape
        landmarks = []
        for lm in face_landmarks.landmark:
            landmarks.append((lm.x, lm.y, lm.z))

        embedding = compute_embedding_from_landmarks(landmarks)
        xs = [p[0] for p in landmarks]
        ys = [p[1] for p in landmarks]
        left = int(min(xs) * w)
        right = int(max(xs) * w)
        top = int(min(ys) * h)
        bottom = int(max(ys) * h)
        cv2.rectangle(frame_bgr, (left, top), (right, bottom), (0, 255, 0), 2)
        return embedding, frame_bgr

    def register_face(self, user_id, username, image_path=None):
        """Register a new face for a user"""
        safe_print(f"\n[INFO] Registering face for user: {username}")

        if image_path:
            img_bgr = cv2.imread(image_path)
            if img_bgr is None:
                safe_print("[ERROR] Could not read image file")
                return False
            image_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
            embedding = self.image_to_embedding(image_rgb)
            if embedding is None:
                safe_print("[ERROR] No face detected in image")
                return False
        else:
            safe_print("[INFO] Opening camera... Press SPACE to capture, ESC to cancel")
            video_capture = cv2.VideoCapture(0)
            captured = False
            captured_embedding = None

            while True:
                ret, frame = video_capture.read()
                if not ret:
                    safe_print("[ERROR] Failed to capture video")
                    video_capture.release()
                    return False

                emb, annotated = self.webcam_frame_to_embedding(frame.copy())
                if emb is not None:
                    cv2.putText(annotated, "Face detected - press SPACE to capture", (10, 30),
                                cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
                else:
                    cv2.putText(annotated, "No face detected - position yourself", (10, 30),
                                cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 255), 2)

                cv2.imshow('Register Face - PerFran (MediaPipe)', annotated)
                key = cv2.waitKey(1) & 0xFF
                if key == 27:
                    safe_print("[ERROR] Registration cancelled")
                    video_capture.release()
                    cv2.destroyAllWindows()
                    return False
                elif key == 32:
                    emb_final, _ = self.webcam_frame_to_embedding(frame.copy())
                    if emb_final is None:
                        safe_print("[ERROR] No face detected. Please try again.")
                        continue
                    captured_embedding = emb_final
                    captured = True
                    break

            video_capture.release()
            cv2.destroyAllWindows()
            if not captured:
                return False
            embedding = captured_embedding

        # Allow forced registration to bypass duplicate-check (used by server-side fallback)
        force_register = os.environ.get('FORCE_REGISTER', '0') == '1'
        print(json.dumps({'debug': 'register_debug', 'force_register': force_register, 'known_count': len(self.known_embeddings)}))
        if len(self.known_embeddings) > 0 and not force_register:
            dists = [embedding_distance(e, embedding) for e in self.known_embeddings]
            valid = [(i, d) for i, d in enumerate(dists) if d is not None]
            if valid:
                best_idx, best_dist = min(valid, key=lambda x: x[1])
                dim = np.asarray(self.known_embeddings[best_idx]).flatten().size
                if dim == 128:
                    threshold = 1.0
                elif dim == 512:
                    threshold = 0.6
                else:
                    threshold = 0.6
                if best_dist < threshold:
                    safe_print(f"[WARN] This face is already registered to user: {self.known_names[best_idx]} (dist {best_dist:.3f})")
                    return False

        self.known_embeddings.append(embedding)
        self.known_ids.append(user_id)
        self.known_names.append(username)
        try:
            self.save_known_embeddings()
            print(json.dumps({'debug': 'face_registered', 'username': username, 'known_count': len(self.known_embeddings)}))
            return True
        except Exception as e:
            print(json.dumps({'debug': 'save_failed', 'error': str(e)}))
            return False

    def recognize_face(self, timeout=30):
        safe_print("\n[INFO] Starting face recognition login...")
        safe_print(f"⏱ Timeout: {timeout} seconds")

        video_capture = cv2.VideoCapture(0)
        start_time = datetime.now()
        recognized_user = None

        while True:
            ret, frame = video_capture.read()
            if not ret:
                safe_print("[ERROR] Failed to capture video")
                break

            elapsed = (datetime.now() - start_time).seconds
            if elapsed > timeout:
                safe_print(f"[INFO] Timeout reached ({timeout}s)")
                break

            embedding, annotated = self.webcam_frame_to_embedding(frame.copy())

            remaining = timeout - elapsed
            cv2.putText(annotated, f"Time remaining: {remaining}s", (10, 30),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 255), 2)
            cv2.putText(annotated, "Position your face in the frame", (10, 60),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
            cv2.putText(annotated, "Press ESC to cancel", (10, 90),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 0, 255), 2)

            if embedding is not None and len(self.known_embeddings) > 0:
                dists = [embedding_distance(e, embedding) for e in self.known_embeddings]
                valid = [(i, d) for i, d in enumerate(dists) if d is not None]
                if valid:
                    best_idx, best_dist = min(valid, key=lambda x: x[1])
                    dim = np.asarray(self.known_embeddings[best_idx]).flatten().size
                    if dim == 128:
                        threshold = 1.0
                    elif dim == 512:
                        threshold = 0.6
                    else:
                        threshold = 0.6
                    if best_dist < threshold:
                        confidence = max(0.0, min(1.0, (threshold - best_dist) / threshold)) * 100.0
                        user_id = self.known_ids[best_idx]
                        username = self.known_names[best_idx]
                        cv2.putText(annotated, f"{username} ({confidence:.1f}%)", (10, 130),
                                    cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2)
                        safe_print(f"✓ Recognized: {username} (dist {best_dist:.3f} => confidence {confidence:.1f}%)")
                        recognized_user = {'user_id': user_id, 'username': username, 'confidence': confidence}
                        cv2.imshow('Face Recognition Login - PerFran (MediaPipe)', annotated)
                        cv2.waitKey(500)
                        break
                    else:
                        cv2.putText(annotated, f"Unknown (dist {best_dist:.2f})", (10, 130),
                                    cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 0, 255), 2)

            cv2.imshow('Face Recognition Login - PerFran (MediaPipe)', annotated)
            key = cv2.waitKey(1) & 0xFF
            if key == 27:
                safe_print(f"[INFO] Login cancelled")
                break

        video_capture.release()
        cv2.destroyAllWindows()
        return recognized_user

    def create_session(self, user_id):
        try:
            conn = self.connect_db()
            cursor = conn.cursor()
            cursor.execute("SELECT * FROM users WHERE id = %s", (user_id,))
            user = cursor.fetchone()
            if user:
                cursor.execute("""
                    INSERT INTO login_logs (user_id, login_method, login_time)
                    VALUES (%s, 'face_recognition_mediapipe', NOW())
                """, (user_id,))
                conn.commit()
                conn.close()
                safe_print(f"[INFO] Session created for user ID: {user_id}")
                return True
            conn.close()
            return False
        except Exception as e:
            safe_print(f"[ERROR] Database error: {e}")
            return False

def main():
    db_config = {
        'host': 'localhost',
        'user': 'root',
        'password': '',
        'database': '2a10_projet'
    }

    fr_system = FaceRecognitionLogin(db_config)

    # Always expose how many known embeddings were loaded (helpful when invoked from PHP)
    try:
        print(json.dumps({'debug': 'known_embeddings_count', 'count': len(fr_system.known_embeddings), 'names': fr_system.known_names}))
    except Exception as _e:
        print(json.dumps({'debug': 'known_embeddings_count_error', 'error': str(_e)}))

    print("\n" + "="*50)
    print("  PerFran - Face Recognition (MediaPipe Embeddings)")
    print("="*50)
    print("\nOptions:")
    print("1. Register new face")
    print("2. Login with face recognition")
    print("3. Exit")

    while True:
        choice = input("\nEnter your choice (1-3): ").strip()
        if choice == '1':
            user_id = input("Enter user ID: ").strip()
            username = input("Enter username: ").strip()
            image_path = input("Optional image path (leave empty to use webcam): ").strip()
            image_path = image_path if image_path else None
            if user_id and username:
                fr_system.register_face(user_id, username, image_path=image_path)
            else:
                print("[ERROR] Invalid input")
        elif choice == '2':
            result = fr_system.recognize_face(timeout=30)
            if result:
                print(f"\n✓ Login successful!")
                print(f"   User: {result['username']}")
                print(f"   Confidence: {result['confidence']:.1f}%")
                fr_system.create_session(result['user_id'])
                print("\nJSON Response:")
                print(json.dumps(result, indent=2))
            else:
                print("\n[ERROR] Face recognition failed")
        elif choice == '3':
            print("\n[INFO] Goodbye!")
            break
        else:
            print("[ERROR] Invalid choice. Please enter 1, 2, or 3.")

def api_main():
    import argparse
    parser = argparse.ArgumentParser(description='Face Recognition API')
    parser.add_argument('--mode', choices=['register', 'login'], required=True)
    parser.add_argument('--input', required=True, help='Path to input image')
    parser.add_argument('--force_register', action='store_true', help='Bypass duplicate check and force register')
    parser.add_argument('--user_id', required=False)
    parser.add_argument('--username', required=False)
    args = parser.parse_args()

    db_config = {
        'host': 'localhost',
        'user': 'root',
        'password': '',
        'database': '2a10_projet'
    }
    fr_system = FaceRecognitionLogin(db_config)

    if args.mode == 'register':
        if not args.user_id or not args.username:
            print('{"success": false, "error": "Missing user_id or username"}')
            return
        if getattr(args, 'force_register', False):
            os.environ['FORCE_REGISTER'] = '1'
        success = fr_system.register_face(args.user_id, args.username, image_path=args.input)
        if success:
            # Also persist encrypted embedding into DB so web/API consumers can find it
            try:
                import pickle
                with open(args.input, 'rb') as f:
                    pass
                img_bgr = cv2.imread(args.input)
                image_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
                emb = fr_system.image_to_embedding(image_rgb)
                if emb is not None:
                    conn = fr_system.connect_db()
                    cur = conn.cursor()
                    pickled = pickle.dumps(emb.tolist())
                    enc = fernet.encrypt(pickled)
                    cur.execute("INSERT INTO user_face_embeddings (user_uid, embedding, method) VALUES (%s, %s, %s)", (int(args.user_id) if str(args.user_id).isdigit() else None, enc, 'mediapipe'))
                    conn.commit()
                    cur.close()
                    conn.close()
            except Exception:
                pass
            print('{"success": true}')
        else:
            print('{"success": false, "error": "Face registration failed"}')
    elif args.mode == 'login':
        # Debug uploaded input file (Apache-run visibility)
        try:
            debug_file(args.input)
        except Exception:
            pass

        try:
            import hashlib
            with open(args.input, 'rb') as f:
                sha1 = hashlib.sha1(f.read()).hexdigest()
        except Exception:
            sha1 = None
        print(json.dumps({'debug': 'input_sha1', 'path': args.input, 'sha1': sha1}))
        img_bgr = cv2.imread(args.input)
        if img_bgr is None:
            try:
                import hashlib
                size = os.path.getsize(args.input)
                with open(args.input, 'rb') as f:
                    digest = hashlib.sha1(f.read()).hexdigest()
            except Exception:
                size = None
                digest = None
            print(json.dumps({'success': False, 'error': 'Could not read image', 'path': args.input, 'size': size, 'sha1': digest}))
            return
        else:
            try:
                h, w, c = img_bgr.shape
            except Exception:
                h = w = c = None
            print(json.dumps({'debug': 'image_loaded', 'path': args.input, 'shape': [h,w,c]}))
        image_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
        embedding = fr_system.image_to_embedding(image_rgb)
        if embedding is None:
            print('{"success": false, "error": "No face detected"}')
            return
        if len(fr_system.known_embeddings) == 0:
            print('{"success": false, "error": "No known faces"}')
            return
        # Use distance-based matching (L2-normalized) for API instead of raw cosine similarity
        dists = [embedding_distance(e, embedding) for e in fr_system.known_embeddings]
        try:
            print(json.dumps({'debug': 'computed_dists', 'dists': [float(d) if d is not None else None for d in dists]}))
        except Exception:
            pass
        valid = [(i, d) for i, d in enumerate(dists) if d is not None]
        if not valid:
            print('{"success": false, "error": "Face not recognized"}')
            return
        best_idx, best_dist = min(valid, key=lambda x: x[1])
        try:
            print(json.dumps({'debug': 'best_match', 'idx': int(best_idx), 'best_dist': float(best_dist)}))
        except Exception:
            pass
        dim = np.asarray(fr_system.known_embeddings[best_idx]).flatten().size
        if dim == 128:
            threshold = 1.0
        elif dim == 512:
            threshold = 0.6
        else:
            threshold = 0.6

        if best_dist < threshold:
            confidence = max(0.0, min(1.0, (threshold - best_dist) / threshold))
            user_id = fr_system.known_ids[best_idx]
            username = fr_system.known_names[best_idx]
            print(json.dumps({"success": True, "user_id": str(user_id), "username": username, "confidence": float(confidence)}))
        else:
            print(json.dumps({"success": False, "error": "Face not recognized", "best_dist": float(best_dist)}))

if __name__ == "__main__":
    def delete_face(user_id, username):
        db_config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': '2a10_projet'
        }
        fr_system = FaceRecognitionLogin(db_config)
        changed = False
        new_emb = []
        new_ids = []
        new_names = []
        for i, id_ in enumerate(fr_system.known_ids):
            if str(id_) == str(user_id) or fr_system.known_names[i] == username:
                changed = True
            else:
                new_emb.append(fr_system.known_embeddings[i])
                new_ids.append(id_)
                new_names.append(fr_system.known_names[i])
        if changed:
            fr_system.known_embeddings = new_emb
            fr_system.known_ids = new_ids
            fr_system.known_names = new_names
            fr_system.save_known_embeddings()
            return {'success': True}
        return {'success': False, 'error': 'No face data found'}

    parser = argparse.ArgumentParser()
    parser.add_argument('--mode', choices=['register', 'login', 'delete'])
    parser.add_argument('--input', required=False)
    parser.add_argument('--user_id', required=False)
    parser.add_argument('--username', required=False)
    parser.add_argument('--force_register', action='store_true')
    args = parser.parse_args()

    if args.mode == 'delete':
        if not args.user_id or not args.username:
            print(json.dumps({'success': False, 'error': 'Missing user_id or username'}))
        else:
            result = delete_face(args.user_id, args.username)
            print(json.dumps(result))
    elif args.mode:
        api_main()
    else:
        main()