import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

from db_config import get_connection

db = get_connection()

pipeline = [
    {
        "$project": {
            "prediction": {
                "$ifNull": [
                    "$result",
                    {"$ifNull": ["$risk_level", "Average"]}
                ]
            }
        }
    },
    {
        "$group": {
            "_id": "$prediction",
            "count": {"$sum": 1}
        }
    }
]

results = list(db.prediction_history.aggregate(pipeline))

labels = []
values = []

for row in results:
    labels.append(row["_id"])
    values.append(row["count"])

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