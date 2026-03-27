# 🎓 Student Result Management System
## Complete Setup Guide for WAMP Server

---

## 📁 FOLDER STRUCTURE

```
student_result_system/
├── index.php                    ← Login page (admin + student)
├── config.php                   ← Database connection & helper functions
│
├── includes/
│   ├── header.php               ← HTML head + global CSS
│   ├── footer.php               ← Page footer
│   └── admin_nav.php            ← Admin navigation bar
│
├── admin/
│   ├── dashboard.php            ← Admin home with stats
│   ├── students.php             ← View/search/delete students
│   ├── add_student.php          ← Add new student
│   ├── edit_student.php         ← Edit student details
│   ├── subjects.php             ← Manage subjects
│   ├── marks.php                ← Enter/update marks
│   ├── results.php              ← View all results
│   └── logout.php               ← Logout handler
│
├── student/
│   ├── result.php               ← Student views their result
│   ├── print_result.php         ← Printable result card
│   └── logout.php               ← Student logout
│
└── database/
    └── setup.sql                ← Database schema + sample data
```

---

## 🛠️ STEP-BY-STEP INSTALLATION

### STEP 1 — Install & Start WAMP Server
1. Download WAMP from: https://www.wampserver.com
2. Install it (follow the installer, use defaults)
3. Launch WAMP — the system tray icon should turn **GREEN**
4. If it stays orange/red, check that port 80 is free (stop Skype or IIS if needed)

---

### STEP 2 — Copy Project Files
1. Open Windows Explorer
2. Navigate to: `C:\wamp64\www\`
   (or `C:\wamp\www\` depending on your version)
3. Create a new folder called `student_result_system`
4. Copy ALL project files into this folder

Your path should look like:
```
C:\wamp64\www\student_result_system\index.php
C:\wamp64\www\student_result_system\config.php
... etc
```

---

### STEP 3 — Create the Database

**Option A — Using phpMyAdmin (Recommended for Beginners)**

1. Open your browser
2. Go to: http://localhost/phpmyadmin
3. Login (default: username = `root`, password = leave blank)
4. Click **"New"** in the left sidebar to create a new database
5. Type `student_result_db` as the name, click **Create**
6. Click on your new `student_result_db` database in the left panel
7. Click the **"SQL"** tab at the top
8. Open the file `database/setup.sql` in Notepad
9. Copy ALL the SQL code
10. Paste it into the SQL box in phpMyAdmin
11. Click **"Go"** to execute
12. ✅ You should see "1 row affected" type messages — database is ready!

**Option B — Using MySQL Command Line**
```bash
mysql -u root -p
# (press Enter when asked for password)
source C:/wamp64/www/student_result_system/database/setup.sql
exit
```

---

### STEP 4 — Configure Database Connection
Open `config.php` in a text editor and verify these settings match your WAMP:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password (blank by default in WAMP)
define('DB_NAME', 'student_result_db');
```

> 💡 If you set a MySQL password during WAMP setup, enter it in DB_PASS

---

### STEP 5 — Open the Project

1. Open your browser
2. Go to: **http://localhost/student_result_system/**
3. You should see the Login page! 🎉

---

## 🔐 DEFAULT LOGIN CREDENTIALS

### Admin Login
| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

### Student Login (Demo accounts)
| Roll Number  | Password     | Name             |
|--------------|--------------|------------------|
| BCA2024001   | BCA2024001   | Ravi Kumar Sharma |
| BCA2024002   | BCA2024002   | Priya Singh       |
| MCA2024001   | MCA2024001   | Amit Patel        |

> ✅ Student password = their roll number (by default)

---

## 🌟 FEATURES LIST

### Admin Features
- ✅ Secure admin login/logout with sessions
- ✅ Dashboard with stats (students, subjects, results)
- ✅ Add/Edit/Delete students with form validation
- ✅ Search students by name or roll number
- ✅ Add/Edit/Delete subjects (course + semester specific)
- ✅ Enter marks per student per subject
- ✅ Auto-calculates: Total, Percentage, Grade, Pass/Fail
- ✅ View all results in beautiful cards
- ✅ Print/Download result for any student

### Student Features
- ✅ Login with roll number + password
- ✅ View their own result card
- ✅ Subject-wise marks + grade
- ✅ Print/Download their result

### Technical Features
- ✅ JavaScript form validation (client-side)
- ✅ PHP server-side validation
- ✅ SQL injection prevention (mysqli_real_escape_string)
- ✅ MD5 password hashing
- ✅ Session-based authentication
- ✅ Role-based access (admin/student)
- ✅ Responsive design (mobile-friendly)

---

## 📊 GRADE SCALE

| Grade | Percentage  |
|-------|------------|
| A+    | 90% – 100% |
| A     | 80% – 89%  |
| B+    | 70% – 79%  |
| B     | 60% – 69%  |
| C     | 50% – 59%  |
| D     | 40% – 49%  |
| F     | Below 40%  |

---

## 🔧 COMMON ISSUES & FIXES

### ❌ "Database Connection Failed"
- Make sure WAMP is running (green icon in tray)
- Check DB_HOST, DB_USER, DB_PASS in config.php
- Verify `student_result_db` database was created

### ❌ "Page Not Found" / 404 Error
- Make sure the folder is named exactly `student_result_system`
- Verify files are in `C:\wamp64\www\student_result_system\`
- Try accessing: http://localhost/student_result_system/index.php

### ❌ "No subjects found" when entering marks
- Go to Admin → Subjects
- Add subjects with the same **Course** and **Semester** as the student

### ❌ phpMyAdmin login fails
- Username: `root`, Password: (blank)
- Or check WAMP tray → MySQL → MySQL Console for credentials

---

## 📌 HOW TO ADD A NEW STUDENT & ENTER MARKS

1. Login as Admin
2. Go to **Students** → Click **Add Student**
3. Fill in details: Roll No, Name, Course, Semester, Password
4. Go to **Subjects** → Add subjects for that Course/Semester
5. Go to **Marks** → Select the student → Enter marks
6. Go to **Results** → View the generated result card
7. Click **Print** to print/download the result

---

## 🗄️ DATABASE TABLES

```
admin          → id, username, password, full_name
students       → id, roll_number, full_name, email, phone, course, semester, password
subjects       → id, subject_code, subject_name, max_marks, pass_marks, semester, course
results        → id, student_id, subject_id, marks_obtained, exam_year
```

---

## 📦 TECHNOLOGIES USED

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7+ (Core PHP, no frameworks)
- **Database**: MySQL
- **Server**: WAMP (Windows, Apache, MySQL, PHP)
- **Fonts**: Google Fonts (Playfair Display + Source Sans 3)

---

Built for educational/college project purposes.
