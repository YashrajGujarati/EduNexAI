import os
import pandas as pd
import seaborn as sns
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

from db_config import get_connection

db = get_connection()

pipeline = [
    {
        "$lookup": {
            "from": "students",
            "localField": "id",
            "foreignField": "user_id",
            "as": "student"
        }
    },
    {"$unwind": "$student"},
    {
        "$lookup": {
            "from": "marks",
            "localField": "student.student_id",
            "foreignField": "student_id",
            "as": "marks_data"
        }
    },
    {
        "$project": {
            "name": "$name",
            "attendance": "$student.attendance",
            "marks": {"$avg": "$marks_data.total_marks"}
        }
    }
]

data = list(db.users.aggregate(pipeline))
df = pd.DataFrame(data)

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