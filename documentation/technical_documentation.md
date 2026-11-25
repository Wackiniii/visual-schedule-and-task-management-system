Technical Documentation   
Web-Based Visual Schedule and Task Management System  
Version: 1.0

1\. System Overview and Architecture  
1.1 Architectural Design

- Frontend: The frontend of the system is developed using HTML5, CSS3. and JS that serves as the frontend server (http\://localhost:3000) and is used for communication between the backend with fetch API (js/api.js) via JSON payloads.  
- Backend: The backend of the system is developed using PHP 8+ that serves as the backend server (http\://localhost:5000) This processes different HTTP requests (GET, POST, PUT, PATCH, and DELETE).   
- Database: Powered with MySQL (visual\_schedule\_db).

1.2 System Requirements

- Server Environment: XAMPP (Apache & MySQL), PHP 8.0 or higher.  
- Client Environment: Browser that supports HTML5 and Web Speech API  
- System Hardware Minimum: Dual-core processor, 4 GB RAM, 500 MB free disk space, desktop or tablet display (\$\\ge 768\\text{px}\$ recommended).

2\. Website Security and Access Control  
2.1  Password Encryption

- Uses password\_hash with bycrypt algorithm  
- Authenticates with password\_verify()  
- No plaintext of password should be seen in the database or stored in api response.

2.2 Strict Data Isolation

- Every API route are made sure to isolate the specific user\_id only before performing crud operation, especially for child tables like “children”, “schedule”, and “tasks”.

2.3 SQL Injection (SQLi) Prevention

- bind\_param is implemented to prevent unknown injection in the system.

**3\. Database Schema and Data Dictionary**  
![][image1]  
3.1  users table

| users |  |
| ----- | :---- |
| id \[PK\] | INT |
| name  | VARCHAR(100) |
| email  | VARCHAR(150) |
| password\_hash  | VARCHAR(255) |
| role | ENUM('Caregiver', 'Occupational Therapist', 'SPED Teacher') |
| created\_at  | TIMESTAMP |

3.2 children table

| children |  |
| ----- | :---- |
| id \[PK\]  | INT |
| user\_id \[FK\] | INT |
| name | VARCHAR(100) |
| age | INT |
| delay\_domain | VARCHAR(100) |
| notes | TEXT |
| photo\_url | VARCHAR(255) |
| created\_at | TIMESTAMP |

3.3 schedules table

| schedules |  |
| ----- | :---- |
| id \[PK\]  | INT |
| child\_id \[FK\] | INT |
| date | DATE |
| created\_at | TIMESTAMP |

3.4 tasks table

| tasks |  |
| ----- | :---- |
| id \[PK\] | INT |
| schedule\_id \[FK\] | INT |
| name | VARCHAR(150) |
| icon | VARCHAR(100) |
| time | TIME |

**4\. REST API Documentation**  
Base URL: http\://localhost:5000/routes/  
Content-Type: application/json

4.1 Authentication (auth.php)  
4.1.1 Register User: POST /routes/auth.php?action=register  
{  
  "name": "Joe Padilla",  
  "email": "joepadilla@test.com",  
  "password": "v3RYH4RDP4SSW0RD\_123",  
  "role": "Caregiver"  
}

- Success Response (201 Created): {"status": "success", "message": "User registered successfully.", "user\_id": 1}  
  4.1.2 Login User: POST /routes/auth.php?action=login

  {"email": "joepadilla@test.com", "password": "v3RYH4RDP4SSW0RD\_123",}  
- Success Response (200 OK): {"status": "success", "user": {"id": 1, "name": "Maria Santos", "role": "Caregiver"}}

4.2 Child Profiles (children.php)  
4.2.1 Get All Children: GET /routes/children.php?user\_id=1  
4.2.2 Create Child Profile: POST /routes/children.php  
{  
  "user\_id": 1,  
  "name": "Leo",  
  "age": 6,  
  "delay\_domain": "Autism Spectrum Disorder",  
  "notes": "Prefers gentle chimes over loud alarms",  
  "photo\_url": "assets/avatars/boy1.png"  
}

- Update Child Profile: PUT /routes/children.php?id=1  
- Delete Child Profile: DELETE /routes/children.php?id=1\&user\_id=1

4.3 Visual Schedules (schedules.php)  
4.3.1 Get Child Schedules: GET /routes/schedules.php?user\_id=1\&child\_id=1  
4.3.2 Create Schedule: POST /routes/schedules.php  
{  
"user\_id": 1,   
"child\_id": 1,   
"date": "2026-11-24"  
}  
4.3.4 Update Schedule: PUT /routes/schedules.php?id=1  
4.3.5 Delete Schedule: DELETE /routes/schedules.php?id=1\&user\_id=1

4.4 Routine Tasks (tasks.php)  
4.4.1 Get Tasks by Schedule: GET /routes/tasks.php?user\_id=1\&schedule\_id=1  
4.4.2 Create Task to Schedule: POST /routes/tasks.php  
{  
  "user\_id": 1,  
  "schedule\_id": 1,  
  "name": "Wash my hands",  
  "icon": "fa-hands-wash",  
  "time": "08:00:00",  
  "duration": 5,  
  "status": "pending"  
}  
4.4.3 Update Task / Mark Completed: PUT /routes/tasks.php?id=1  
{  
  "user\_id": 1,  
  "schedule\_id": 1,  
  "name": "Wash my hands",  
  "icon": "fa-hands-wash",  
  "time": "08:00:00",  
  "duration": 5,  
  "status": "completed"  
}  
4.4.4 Delete Task: DELETE /routes/tasks.php?id=1\&user\_id=1  
4.5 System Health Check (health.php)  
4.5.1 Check Database: GET /routes/health.php

- Service Unavailable (503 Service Unavailable): Returned if MySQL cannot be reached ({"status": "error", "message": "Cannot connect to the database..."}).

4.6 Task Sets (task\_sets.php)  
4.6.1 Get All Task Sets: GET /routes/task\_sets.php?user\_id=1  
4.6.2 Create Task Sets: POST /routes/task\_sets.php  
{  
  "user\_id": 1,  
  "name": "Morning Hygiene Routine",  
  "tasks": \[  
    {  
      "name": "Brush Teeth",  
      "icon": "fa-tooth",  
      "time": "07:30:00",  
      "duration": 5  
    }  
  \]  
}

- Success Response (201 Created): Returns id, name, and the validated tasks array  
  4.6.3 Delete Task Set: DELETE /routes/task\_sets.php?id=1\&user\_id=1  
- Success Response (200 OK): {"id": 1}

4.7 User Verification (users.php)  
4.7.1 Verifying user if exist: GET /routes/users.php?id=1\&email=joepadilla@test.com

- Success Response (200 OK):

  {

    "name": "Joe Padilla",

    "email": "joepadilla@test.com",

    "password": "v3RYH4RDP4SSW0RD\_123",

    "role": "Caregiver"

  }

**5\. Local Installation and Deployment Instructions**  
5.1 Install Prerequisites: Install XAMPP with Apache and MySQL enabled  
5.2. Setting up the Database (One-Time Setup)

- Make sure that Apache and MySQL are enabled  
- Click admin right beside the MySQL  
- Import the schema from the backend/schema.sql or copy paste the schema to SQL tab

5.3 Verify database configuration: Ensure that your database credentials from backend/config/db.php are matched to your local MySQL setup **(host: localhost, user: root, password: "").**  
5.4.1 Running the server: Ensure that the MySQL is enabled in XAMPP control panel then copy and paste the following in the terminal

+  cd backend

   C:\\xampp\\php\\php.exe \-S localhost:5000

+  cd frontend

   C:\\xampp\\php\\php.exe \-S localhost:3000

- Leave this open when deploying the website

5.4.2 Running the server: You can also just run start.bat to start and launch the website and stop.bat to close and terminate the server.  


[image1]: <data:image/png;base64,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>