import sys
import pandas as pd
from sklearn.tree import DecisionTreeClassifier

data = pd.DataFrame({
    "attendance": [95, 85, 75, 65, 50, 40],
    "marks": [90, 80, 70, 60, 50, 35],
    "result": [
        "Excellent",
        "Good",
        "Good",
        "Average",
        "Average",
        "Poor"
    ]
})

X = data[["attendance", "marks"]]
y = data["result"]

model = DecisionTreeClassifier()
model.fit(X, y)

attendance = float(sys.argv[1])
marks = float(sys.argv[2])

input_data = pd.DataFrame(
    [[attendance, marks]],
    columns=["attendance", "marks"]
)

prediction = model.predict(input_data)

print(prediction[0])