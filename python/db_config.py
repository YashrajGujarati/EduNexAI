import os
import pymongo

def get_mongo_client():
    """
    Creates and returns a MongoClient instance using MONGODB_URI or default localhost.
    """
    uri = os.getenv('MONGODB_URI') or os.getenv('MONGO_URL') or 'mongodb://localhost:27017'
    return pymongo.MongoClient(uri)

def get_connection():
    """
    Establishes a MongoDB connection using environment variables,
    supporting MONGODB_URI and MONGODB_DATABASE.
    Returns the MongoDB database instance.
    """
    client = get_mongo_client()
    db_name = os.getenv('MONGODB_DATABASE') or os.getenv('DB_NAME') or 'student_ai_system'
    return client[db_name]
