import os
import sys
import subprocess

script_dir = os.path.dirname(os.path.abspath(__file__))

scripts = ["bar_chart.py", "pie_chart.py", "heatmap.py", "report.py"]
for script in scripts:
    script_path = os.path.join(script_dir, script)
    subprocess.run([sys.executable, script_path], check=False)

print("All reports generated successfully.")