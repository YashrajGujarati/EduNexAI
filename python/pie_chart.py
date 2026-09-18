import os
import mysql.connector
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

conn = mysql.connector.connect(
    host=os.getenv('DB_HOST', 'localhost'),
    user=os.getenv('DB_USER', 'root'),
    password=os.getenv('DB_PASSWORD', ''),
    database=os.getenv('DB_NAME', 'student_ai_system')
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

if values:
    plt.pie(
        values,
        labels=labels,
        autopct="%1.1f%%",
        startangle=90
    )

plt.title("Student Performance Analysis")

script_dir = os.path.dirname(os.path.abspath(__file__))
output_dir = os.path.join(script_dir, "output")
os.makedirs(output_dir, exist_ok=True)

plt.savefig(os.path.join(output_dir, "pie_chart.png"))
plt.close()

cursor.close()
conn.close()