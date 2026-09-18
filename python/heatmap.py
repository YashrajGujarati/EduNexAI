import os
import mysql.connector
import pandas as pd
import seaborn as sns
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

conn = mysql.connector.connect(
    host=os.getenv('DB_HOST', 'localhost'),
    user=os.getenv('DB_USER', 'root'),
    password=os.getenv('DB_PASSWORD', ''),
    database=os.getenv('DB_NAME', 'student_ai_system')
)

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

plt.figure(figsize=(10, 6))

if not df.empty and "attendance" in df.columns and "marks" in df.columns:
    df["attendance"] = pd.to_numeric(df["attendance"], errors='coerce').fillna(0)
    df["marks"] = pd.to_numeric(df["marks"], errors='coerce').fillna(0)
    sns.heatmap(
        df[["attendance", "marks"]],
        annot=True,
        cmap="YlGnBu"
    )

plt.title("Student Performance Heatmap")

script_dir = os.path.dirname(os.path.abspath(__file__))
output_dir = os.path.join(script_dir, "output")
os.makedirs(output_dir, exist_ok=True)

plt.savefig(os.path.join(output_dir, "heatmap.png"))
plt.close()

conn.close()