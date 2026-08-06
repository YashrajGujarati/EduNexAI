import mysql.connector
import matplotlib.pyplot as plt

conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="student_ai_system"
)

cursor = conn.cursor()

query = """
SELECT prediction, COUNT(*)
FROM prediction_history
GROUP BY prediction
"""

cursor.execute(query)

result = cursor.fetchall()

labels = []
values = []

for row in result:
    labels.append(row[0])
    values.append(row[1])

plt.figure(figsize=(8, 8))

plt.pie(
    values,
    labels=labels,
    autopct="%1.1f%%",
    startangle=90
)

plt.title("Student Performance Analysis")

plt.savefig(
    r"D:\Xampp\htdocs\EduNexAI\python\output\pie_chart.png"
)

plt.show()

cursor.close()
conn.close()