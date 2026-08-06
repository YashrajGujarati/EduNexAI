import mysql.connector
import matplotlib.pyplot as plt
import seaborn as sns

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

sns.set_theme(style="whitegrid")

plt.figure(figsize=(10, 6))

sns.barplot(
    x=labels,
    y=values
)

plt.title("Student Performance Report")
plt.xlabel("Prediction")
plt.ylabel("Number of Students")

plt.savefig(
    r"D:\Xampp\htdocs\EduNexAI\python\output\bar_chart.png"
)

plt.show()

cursor.close()
conn.close()