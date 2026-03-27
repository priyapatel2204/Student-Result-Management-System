<?php
// ============================================================
// admin/add_student.php - Add New Student
// ============================================================
session_start();
require_once '../config.php';
requireAdminLogin();

$pageTitle = 'Add Student';
$msg = $msgType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll     = strtoupper(sanitize($conn, $_POST['roll_number']));
    $name     = sanitize($conn, $_POST['full_name']);
    $email    = sanitize($conn, $_POST['email']);
    $phone    = sanitize($conn, $_POST['phone']);
    $course   = sanitize($conn, $_POST['course']);
    $semester = (int)$_POST['semester'];
    $password = sanitize($conn, $_POST['password']);

    // Validation
    if (empty($roll) || empty($name) || empty($course) || empty($password)) {
        $msg     = 'Roll Number, Name, Course, and Password are required.';
        $msgType = 'danger';
    } elseif ($semester < 1 || $semester > 8) {
        $msg     = 'Semester must be between 1 and 8.';
        $msgType = 'danger';
    } else {
        // Check duplicate roll number
        $check = mysqli_query($conn, "SELECT id FROM students WHERE roll_number='$roll'");
        if (mysqli_num_rows($check) > 0) {
            $msg     = "Roll Number '$roll' already exists.";
            $msgType = 'danger';
        } else {
            $hashedPass = md5($password);
            $sql = "INSERT INTO students (roll_number, full_name, email, phone, course, semester, password)
                    VALUES ('$roll','$name','$email','$phone','$course','$semester','$hashedPass')";
            if (mysqli_query($conn, $sql)) {
                $msg     = "Student '$name' added successfully! Roll: $roll";
                $msgType = 'success';
            } else {
                $msg     = 'Database error: ' . mysqli_error($conn);
                $msgType = 'danger';
            }
        }
    }
}

include '../includes/header.php';
include '../includes/admin_nav.php';
?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> ›
            <a href="students.php">Students</a> › Add
        </div>
        <h1>Add New Student</h1>
        <p>Enroll a new student into the system</p>
    </div>

    <?php if ($msg) echo "<div class='alert alert-$msgType'>$msg</div>"; ?>

    <div class="card">
        <div class="card-header">
            <h2>Student Information</h2>
        </div>

        <form method="POST" action="" onsubmit="return validateAddStudent()">
            <div class="form-grid">
                <!-- Roll Number -->
                <div class="form-group">
                    <label>Roll Number *</label>
                    <input type="text"
                           class="form-control"
                           name="roll_number"
                           id="roll_number"
                           placeholder="e.g. BCA2024001"
                           style="text-transform:uppercase;">
                    <div class="error-msg" id="rollErr"></div>
                </div>

                <!-- Full Name -->
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text"
                           class="form-control"
                           name="full_name"
                           id="full_name"
                           placeholder="Student's full name">
                    <div class="error-msg" id="nameErr"></div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email"
                           class="form-control"
                           name="email"
                           id="email"
                           placeholder="student@email.com">
                    <div class="error-msg" id="emailErr"></div>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text"
                           class="form-control"
                           name="phone"
                           placeholder="10-digit mobile number"
                           maxlength="15">
                </div>

                <!-- Course -->
                <div class="form-group">
                    <label>Course *</label>
                    <select class="form-control" name="course" id="course">
                        <option value="">-- Select Course --</option>
                        <option value="BCA">BCA</option>
                        <option value="MCA">MCA</option>
                        <option value="BSc CS">BSc CS</option>
                        <option value="BBA">BBA</option>
                        <option value="BCom">BCom</option>
                        <option value="BA">BA</option>
                    </select>
                    <div class="error-msg" id="courseErr"></div>
                </div>

                <!-- Semester -->
                <div class="form-group">
                    <label>Semester *</label>
                    <select class="form-control" name="semester" id="semester">
                        <option value="">-- Select Semester --</option>
                        <?php for ($s = 1; $s <= 8; $s++): ?>
                            <option value="<?php echo $s; ?>">Semester <?php echo $s; ?></option>
                        <?php endfor; ?>
                    </select>
                    <div class="error-msg" id="semErr"></div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label>Login Password *</label>
                    <input type="text"
                           class="form-control"
                           name="password"
                           id="password"
                           placeholder="Default: Roll Number">
                    <div class="error-msg" id="passErr"></div>
                    <small style="color:#718096;font-size:12px;margin-top:4px;display:block;">
                        💡 Tip: Set password same as roll number for easy login.
                    </small>
                </div>
            </div>

            <div class="divider"></div>
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary">✅ Add Student</button>
                <a href="students.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function validateAddStudent() {
    let valid = true;
    clearErrors();

    const roll   = document.getElementById('roll_number').value.trim();
    const name   = document.getElementById('full_name').value.trim();
    const email  = document.getElementById('email').value.trim();
    const course = document.getElementById('course').value;
    const sem    = document.getElementById('semester').value;
    const pass   = document.getElementById('password').value.trim();

    if (!roll) { showErr('rollErr', 'Roll number is required.'); valid = false; }
    if (!name) { showErr('nameErr', 'Full name is required.'); valid = false; }
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showErr('emailErr', 'Enter a valid email.'); valid = false;
    }
    if (!course) { showErr('courseErr', 'Please select a course.'); valid = false; }
    if (!sem)    { showErr('semErr', 'Please select a semester.'); valid = false; }
    if (!pass)   { showErr('passErr', 'Password is required.'); valid = false; }
    else if (pass.length < 4) { showErr('passErr', 'Password must be at least 4 characters.'); valid = false; }

    return valid;
}

function showErr(id, msg) {
    document.getElementById(id).textContent = msg;
}

function clearErrors() {
    ['rollErr','nameErr','emailErr','courseErr','semErr','passErr']
        .forEach(id => document.getElementById(id).textContent = '');
}
</script>

<?php include '../includes/footer.php'; ?>
