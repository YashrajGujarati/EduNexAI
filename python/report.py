import mysql.connector
import pandas as pd

conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="student_ai_system"
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

print(df)

df.to_csv(
    r"D:\Xampp\htdocs\EduNexAI\python\output\student_report.csv",
    index=False
)

print("Report generated successfully.")

conn.close()