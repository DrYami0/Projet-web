#!/usr/bin/env python3
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
EXCLUDE_DIRS = {'vendor', '.git', 'node_modules', '__pycache__'}
EXTS = {'.php', '.js', '.py', '.html', '.css', '.json', '.txt', '.md'}

replacements = [
    (re.compile(r'(?i)(?<!PerFranMVC/)(\bController/)', flags=0), 'PerFranMVC/Controller/'),
    (re.compile(r'(?i)(?<!PerFranMVC/)(\bModel/)', flags=0), 'PerFranMVC/Model/'),
    (re.compile(r'(?i)(?<!PerFranMVC/)(\bView/)', flags=0), 'PerFranMVC/View/'),
]

def is_binary(path: Path):
    try:
        data = path.read_bytes()
        return b"\0" in data
    except Exception:
        return True

changed = []
for p in ROOT.rglob('*'):
    if p.is_dir():
        if p.name in EXCLUDE_DIRS:
            # skip subtree
            for _ in p.rglob('*'):
                pass
        continue
    if p.suffix.lower() not in EXTS:
        continue
    if 'PerFranMVC' in str(p):
        # still edit files inside new dir (they may link to PerFranMVC/Controller/ too)
        pass
    if is_binary(p):
        continue
    try:
        s = p.read_text(encoding='utf-8')
    except Exception:
        try:
            s = p.read_text(encoding='latin-1')
        except Exception:
            continue
    orig = s
    # Protect existing PerFranMVC occurrences
    s = s.replace('PerFranMVC/PerFranMVC/', 'PerFranMVC/PerFranMVC/')
    for pattern, repl in replacements:
        s = pattern.sub(repl, s)
    s = s.replace('PerFranMVC/PerFranMVC/', 'PerFranMVC/PerFranMVC/')
    if s != orig:
        bak = p.with_suffix(p.suffix + '.bak')
        bak.write_text(orig, encoding='utf-8')
        p.write_text(s, encoding='utf-8')
        changed.append(str(p.relative_to(ROOT)))

print('Modified files:')
for c in changed:
    print(c)
print('Done. Backups with .bak created for modified files.')
