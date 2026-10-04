import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import seaborn as sns

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