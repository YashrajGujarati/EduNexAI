import os
import pandas as pd

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
            "_id": 0,
            "name": "$name",
            "attendance": "$student.attendance",
            "marks": {"$avg": "$marks_data.total_marks"}
        }
    }
]

data = list(db.users.aggregate(pipeline))
df = pd.DataFrame(data)

script_dir = os.path.dirname(os.path.abspath(__file__))
output_dir = os.path.join(script_dir, "output")
os.makedirs(output_dir, exist_ok=True)

df.to_csv(
    os.path.join(output_dir, "student_report.csv"),
    index=False
)

print("Report generated successfully.")