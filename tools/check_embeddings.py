import mysql.connector

cnx = mysql.connector.connect(host='localhost', user='root', password='', database='2a10_projet')
cur = cnx.cursor()
cur.execute('SELECT id, user_uid, created_at FROM user_face_embeddings WHERE user_uid = %s ORDER BY id DESC LIMIT 5', (1,))
rows = cur.fetchall()
print('found', len(rows), 'rows for user 1')
for r in rows:
    print(r)
cur.close()
cnx.close()
