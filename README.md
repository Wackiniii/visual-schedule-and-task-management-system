# visual-schedule-and-task-management-system
This project aims to build web-based visual schedule and task management system helping children with developmental delays

Subject: Modeling and Simulation
Section: UCOS 3-1
Group 2

Members:
- Bacarro
- Gogola
- Jovellanos
- Lazo
- Maula
- Padilla
- Quimpan
- Rosal
- Sangueza
- Toleran

## Demo Video
`[demo-video](https://drive.google.com/file/d/1CnvP9FdLE0U_445XscbjtwYkwlzjOKmM/view?usp=sharing)`

## Setup Guide

### Prerequisite
- XAMPP Control Panel
- Visual Studio Code

### 0. Cloning Repository
1. Copy our Repo link `https://github.com/Wackiniii/visual-schedule-and-task-management-system.git`


https://github.com/user-attachments/assets/206688a1-4528-4650-b1d2-8099bde622c1


2. Open **Visual Studio Code**
3. Click **Source Control icon** on the left side (or press `Ctrl + Shift + G`)
4. CLick Clone Repository and paste the repo link of the project then select the destination folder on your PC


https://github.com/user-attachments/assets/5afb8776-54a4-45d4-b78f-4ea8930ecaf4


### 1. Setting up Database
1. Open **XAMPP control panel** then start both Apache and MySQL
2. Click Admin button next to MySQL module that will redirect you to phpmyadmin `http://localhost/phpmyadmin`


https://github.com/user-attachments/assets/cf0dc450-b4fc-4cb0-812b-fc45e13a3895


3. Click SQL tab on top of the page
4. Copy the schema from `database/schema.sql`, paste it into the SQL query box then click Go (or import the file `schema.sql`)


https://github.com/user-attachments/assets/83659c17-5625-4239-82b0-56106d01148d


### 2. Setting up PHP built-in server for backend and frontend | Windows 11
1. Open terminal via View > Terminal (or press `` Ctrl + ` ``)
2. Terminal 1  (cmd) (Backend API)
```bash
   cd backend
   C:\xampp\php\php.exe -S localhost:5000
```
- copy the following above
- Leave the terminal running. It should result to `PHP Development Server (http://localhost:5000) started`

3. Terminal 2 (cmd) (Frontend API)
```bash
    cd frontend
    C:\xampp\php\php.exe -S localhost:3000
```
- click + icon and copy the following above
- Leave this terminal running. It should result to `PHP Development Server (http://localhost:3000) started`


https://github.com/user-attachments/assets/014c4e82-f4b9-4fd2-b891-87f060669e0a


### 3. Opening the application
- Copy and paste the link in the browser `http://localhost:3000/`

### 4. Closing the application
- close both terminal by pressing `Ctrl + C`


### 5. One Click Launch
- Once you finished the previous steps you can just click `start.bat` to launch the website and click `stop.bat` to close all running server in your PC.


https://github.com/user-attachments/assets/578194d9-ba5e-49ac-9f05-344316165819




https://github.com/user-attachments/assets/6e103245-ddf5-4def-9851-2ea62254dfb7

