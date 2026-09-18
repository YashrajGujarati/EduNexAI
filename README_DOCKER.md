# 🐳 EduNexAI Docker Setup & Run Guide

Run the EduNexAI website automatically on **any computer** (Windows, macOS, or Linux) with zero configuration bugs, no manual XAMPP setup, and no Python dependency installation required.

---

## 🚀 Quick Start (One-Step Command)

Make sure [Docker Desktop](https://www.docker.com/products/docker-desktop/) is installed and running on your PC.

Open your terminal in the `EduNexAI` project folder and run:

```bash
docker compose up -d --build
```

That's it! Docker will automatically:
1. Build the web application container with PHP 8.2 Apache + Python 3 AI modules + Composer packages.
2. Launch the MySQL database container.
3. Import the database tables and default admin credentials automatically from `database/setup.sql`.

---

## 🌐 Accessing the Application

Once launched, open your web browser and navigate to:

👉 **[http://localhost:8080](http://localhost:8080)**

### 🔑 Default Login Credentials

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@edunex.com` | `admin123` |
| **Faculty** | `faculty@edunex.com` | `faculty123` |
| **Student** | `student@edunex.com` | `student123` |

---

## 🛠 Useful Docker Commands

- **Check Running Containers**:
  ```bash
  docker compose ps
  ```

- **View Logs (Web App & Database)**:
  ```bash
  docker compose logs -f
  ```

- **Stop the Application**:
  ```bash
  docker compose stop
  ```

- **Stop and Remove Containers & Volumes**:
  ```bash
  docker compose down -v
  ```

---

## 🧠 Architecture Overview

- **Web Container (`edunex_web`)**:
  - Apache + PHP 8.2
  - Installed PHP Extensions: `mysqli`, `pdo_mysql`, `gd`, `zip`
  - Integrated Python 3 AI stack: `pandas`, `scikit-learn`, `mysql-connector-python`, `seaborn`, `matplotlib`
- **Database Container (`edunex_db`)**:
  - MySQL 8.0
  - Auto-initializes schema on startup via `/docker-entrypoint-initdb.d/setup.sql`
