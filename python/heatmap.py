import mysql.connector
import pandas as pd
import seaborn as sns
import matplotlib.pyplot as plt

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

plt.figure(figsize=(10, 6))

sns.heatmap(
    df[["attendance", "marks"]],
    annot=True,
    cmap="YlGnBu"
)

plt.title("Student Performance Heatmap")

plt.savefig(
    r"D:\Xampp\htdocs\EduNexAI\python\output\heatmap.png"
)

plt.show()

conn.close()