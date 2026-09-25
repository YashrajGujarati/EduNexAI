import os
import mysql.connector
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import seaborn as sns

conn = mysql.connector.connect(
    host=os.getenv('DB_HOST', 'localhost'),
    user=os.getenv('DB_USER', 'root'),
    password=os.getenv('DB_PASSWORD', ''),
    database=os.getenv('DB_NAME', 'student_ai_system')
)

cursor = conn.cursor()

query = """
SELECT COALESCE(result, risk_level, 'Average') AS prediction, COUNT(*)
FROM prediction_history
GROUP BY COALESCE(result, risk_level, 'Average')
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

if labels:
    sns.barplot(
        x=labels,
        y=values
    )

plt.title("Student Performance Report")
plt.xlabel("Prediction")
plt.ylabel("Number of Students")

script_dir = os.path.dirname(os.path.abspath(__file__))
output_dir = os.path.join(script_dir, "output")
os.makedirs(output_dir, exist_ok=True)

plt.savefig(os.path.join(output_dir, "bar_chart.png"))
plt.close()

cursor.close()
conn.close()