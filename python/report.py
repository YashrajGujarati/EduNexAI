import os
import mysql.connector
import pandas as pd

from db_config import get_connection

conn = get_connection()

query = """
SELECT
users.name,
students.attendance,
AVG(marks.total_marks) AS marks
FROM users
INNER JOIN students
ON users.id = students.user_id
LEFT JOIN marks
ON students.student_id = marks.student_id
GROUP BY users.id
"""

df = pd.read_sql(query, conn)

script_dir = os.path.dirname(os.path.abspath(__file__))
output_dir = os.path.join(script_dir, "output")
os.makedirs(output_dir, exist_ok=True)

df.to_csv(
    os.path.join(output_dir, "student_report.csv"),
    index=False
)

print("Report generated successfully.")

conn.close()