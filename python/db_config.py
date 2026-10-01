import os
import mysql.connector

def get_connection():
    """
    Establishes a MySQL connection using environment variables,
    supporting both DB_* and Railway native MYSQL* variables.
    """
    host = os.getenv('DB_HOST') or os.getenv('MYSQLHOST') or 'localhost'
    user = os.getenv('DB_USER') or os.getenv('MYSQLUSER') or 'root'
    password = os.getenv('DB_PASSWORD') or os.getenv('MYSQLPASSWORD') or ''
    database = os.getenv('DB_NAME') or os.getenv('MYSQLDATABASE') or 'student_ai_system'
    
    port_str = os.getenv('DB_PORT') or os.getenv('MYSQLPORT') or '3306'
    try:
        port = int(port_str)
    except ValueError:
        port = 3306

    return mysql.connector.connect(
        host=host,
        user=user,
        password=password,
        database=database,
        port=port
    )
