# Setup Guide

# 0. Cloning Repository
1. Copy our Repo `https://github.com/Wackiniii/visual-schedule-and-task-management-system.git`
2. Open **Visual Studio Code**
3. Click **Source Control icon** on the left side (or press `Ctrl + Shift + G`)
4. CLick Clone Repository and paste the repo link of the project then select the destination folder on your PC

# 1. Setting up Database
1. Open **XAMPP control panel** then start both Apache and MySQL
2. Click Admin button next to MySQL module that will redirect you to phpmyadmin `http://localhost/phpmyadmin`
3. Click SQL tab on top of the page
4. Copy the schema from `database/schema.sql`, paste it into the SQL query box then click Go

# 2. Setting up PHP built-in server for backend and frontend
1. Open terminal via View > Terminal (or press `` Ctrl + ` ``)
2. Terminal 1 (Backend API)
```bash
   cd backend
   php -S localhost:5000
```
- copy the following above
- Leave the terminal running. It should result to `PHP Development Server (http://localhost:5000) started`

3. Terminal 2 (Frontend API)
```bash
    cd frontend
    php -S localhost:3000
```
- click + icon and copy the following above
- Leave this terminal running. It should result to `PHP Development Server (http://localhost:3000) started`

# 3. Opening the applciation
- Copy and paste the link in the browser `http://localhost:3000/`