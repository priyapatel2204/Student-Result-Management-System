<?php
// ============================================================
// index.php - Login Page (Admin & Student)
// ============================================================
session_start();
require_once 'config.php';

// If already logged in, redirect to proper dashboard
if (isAdminLoggedIn()) {
    redirect('admin/dashboard.php');
}
if (isStudentLoggedIn()) {
    redirect('student/result.php');
}

$error   = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role     = sanitize($conn, $_POST['role'] ?? '');
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = sanitize($conn, $_POST['password'] ?? '');

    // Basic server-side validation
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif ($role === 'admin') {
        // --- Admin Login ---
        $hashed = md5($password);
        $sql    = "SELECT * FROM admin WHERE username='$username' AND password='$hashed'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            redirect('admin/dashboard.php');
        } else {
            $error = 'Invalid admin username or password.';
        }

    } elseif ($role === 'student') {
        // --- Student Login ---
        $hashed = md5($password);
        $sql    = "SELECT * FROM students WHERE roll_number='$username' AND password='$hashed'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $student = mysqli_fetch_assoc($result);
            $_SESSION['student_id']          = $student['id'];
            $_SESSION['student_name']        = $student['full_name'];
            $_SESSION['student_roll']        = $student['roll_number'];
            redirect('student/result.php');
        } else {
            $error = 'Invalid roll number or password.';
        }
    } else {
        $error = 'Please select a login role.';
    }
}

// Check for error/success from URL params (redirects)
if (isset($_GET['error'])) $error = htmlspecialchars($_GET['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo SITE_NAME; ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@300;400;500;600&display=swap');

        * { margin:0; padding:0; box-sizing:border-box; }

        :root {
            --navy:  #1a2744;
            --gold:  #c9a84c;
            --bg:    #f0f2f8;
            --white: #ffffff;
        }

        body {
            font-family: 'Source Sans 3', sans-serif;
            min-height: 100vh;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Decorative background */
        body::before {
            content: '';
            position: fixed;
            top: -120px;
            right: -120px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(201,168,76,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -100px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(26,39,68,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
        }

        /* Logo / Header */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo {
            width: 72px;
            height: 72px;
            background: var(--navy);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(26,39,68,0.25);
        }

        .login-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            color: var(--navy);
            margin-bottom: 6px;
        }

        .login-header p {
            color: #718096;
            font-size: 14px;
        }

        /* Role Tabs */
        .role-tabs {
            display: flex;
            background: #e2e8f0;
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 24px;
        }

        .role-tab {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #718096;
        }

        .role-tab.active {
            background: var(--navy);
            color: var(--white);
            box-shadow: 0 2px 8px rgba(26,39,68,0.2);
        }

        /* Login Card */
        .login-card {
            background: var(--white);
            border-radius: 16px;
            padding: 36px;
            box-shadow: 0 8px 40px rgba(26,39,68,0.12);
            border: 1px solid #e2e8f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 12px;
            color: var(--navy);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 15px;
            color: #2d3748;
            background: #fafbfd;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(26,39,68,0.08);
            background: var(--white);
        }

        .error-msg { color: #c53030; font-size: 12px; margin-top: 5px; }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-danger  { background:#fff5f5; color:#742a2a; border-left:4px solid #fc8181; }
        .alert-success { background:#f0fff4; color:#22543d; border-left:4px solid #48bb78; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 9px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 6px;
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            background: #243460;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(26,39,68,0.3);
        }

        .hint-box {
            background: #f7f9fd;
            border: 1px dashed #cbd5e0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 22px;
            font-size: 13px;
            color: #718096;
        }

        .hint-box strong { color: var(--navy); }

        .hint-box .hint-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .divider { height: 1px; background: #e2e8f0; margin: 18px 0; }

        /* Student hint tab visibility */
        .admin-hint, .student-hint { display: none; }

        @media (max-width: 520px) {
            .login-card { padding: 24px 20px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Header -->
    <div class="login-header">
        <div class="login-logo">🎓</div>
        <h1><?php echo SITE_NAME; ?></h1>
        <p>Manage academic results with ease</p>
    </div>

    <!-- Role Selector Tabs -->
    <div class="role-tabs">
        <button class="role-tab active" id="tab-admin"   onclick="switchRole('admin')">
            🔐 Admin Login
        </button>
        <button class="role-tab"        id="tab-student" onclick="switchRole('student')">
            🎓 Student Login
        </button>
    </div>

    <!-- Login Card -->
    <div class="login-card">
        <?php if ($error): ?>
            <div class="alert alert-danger">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm" onsubmit="return validateForm()">
            <!-- Hidden role field - updated by JS -->
            <input type="hidden" name="role" id="roleField" value="admin">

            <!-- Username field -->
            <div class="form-group">
                <label id="userLabel">👤 Admin Username</label>
                <div class="input-wrap">
                    <span class="input-icon">🔑</span>
                    <input type="text"
                           class="form-control"
                           name="username"
                           id="username"
                           placeholder="Enter username"
                           autocomplete="username">
                </div>
                <div class="error-msg" id="usernameError"></div>
            </div>

            <!-- Password field -->
            <div class="form-group">
                <label>🔒 Password</label>
                <div class="input-wrap">
                    <span class="input-icon">🗝️</span>
                    <input type="password"
                           class="form-control"
                           name="password"
                           id="password"
                           placeholder="Enter password"
                           autocomplete="current-password">
                </div>
                <div class="error-msg" id="passwordError"></div>
            </div>

            <button type="submit" class="btn-login">Sign In →</button>
        </form>

        <!-- Demo Credentials Hint -->
        <div class="hint-box" id="adminHint">
            <strong>Demo Credentials</strong>
            <div class="divider"></div>
            <div class="hint-row"><span>Username:</span> <strong>admin</strong></div>
            <div class="hint-row"><span>Password:</span> <strong>admin123</strong></div>
        </div>

        <div class="hint-box" id="studentHint" style="display:none;">
            <strong>Demo Credentials</strong>
            <div class="divider"></div>
            <div class="hint-row"><span>Roll No:</span> <strong>BCA2024001</strong></div>
            <div class="hint-row"><span>Password:</span> <strong>BCA2024001</strong></div>
        </div>
    </div>
</div>

<script>
// Switch between admin and student login forms
function switchRole(role) {
    document.getElementById('roleField').value = role;

    // Update tabs
    document.getElementById('tab-admin').classList.toggle('active', role === 'admin');
    document.getElementById('tab-student').classList.toggle('active', role === 'student');

    // Update label and placeholder
    const label = document.getElementById('userLabel');
    const input = document.getElementById('username');
    if (role === 'admin') {
        label.textContent = '👤 Admin Username';
        input.placeholder = 'Enter username';
        document.getElementById('adminHint').style.display   = 'block';
        document.getElementById('studentHint').style.display = 'none';
    } else {
        label.textContent = '🎓 Roll Number';
        input.placeholder = 'e.g. BCA2024001';
        document.getElementById('adminHint').style.display   = 'none';
        document.getElementById('studentHint').style.display = 'block';
    }

    // Clear errors
    document.getElementById('usernameError').textContent = '';
    document.getElementById('passwordError').textContent = '';
}

// Client-side form validation
function validateForm() {
    let valid = true;
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    document.getElementById('usernameError').textContent = '';
    document.getElementById('passwordError').textContent = '';

    if (!username) {
        document.getElementById('usernameError').textContent = 'This field is required.';
        valid = false;
    }
    if (!password) {
        document.getElementById('passwordError').textContent = 'Password is required.';
        valid = false;
    } else if (password.length < 4) {
        document.getElementById('passwordError').textContent = 'Password is too short.';
        valid = false;
    }

    return valid;
}
</script>

</body>
</html>
