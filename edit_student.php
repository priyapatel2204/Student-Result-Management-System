<?php
// ============================================================
// admin/edit_student.php - Edit Student Details
// ============================================================
session_start();
require_once '../config.php';
requireAdminLogin();

$pageTitle = 'Edit Student';
$msg = $msgType = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch existing student data
$result  = mysqli_query($conn, "SELECT * FROM students WHERE id=$id");
if (mysqli_num_rows($result) === 0) {
    redirect('students.php');
}
$student = mysqli_fetch_assoc($result);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll     = strtoupper(sanitize($conn, $_POST['roll_number']));
    $name     = sanitize($conn, $_POST['full_name']);
    $email    = sanitize($conn, $_POST['email']);
    $phone    = sanitize($conn, $_POST['phone']);
    $course   = sanitize($conn, $_POST['course']);
    $semester = (int)$_POST['semester'];
    $password = sanitize($conn, $_POST['password']);

    if (empty($roll) || empty($name) || empty($course)) {
        $msg = 'Roll Number, Name, and Course are required.';
        $msgType = 'danger';
    } else {
        // Check duplicate roll number (excluding self)
        $check = mysqli_query($conn,
            "SELECT id FROM students WHERE roll_number='$roll' AND id != $id");
        if (mysqli_num_rows($check) > 0) {
            $msg = "Roll Number '$roll' is already used by another student.";
            $msgType = 'danger';
        } else {
            // Update with or without password change
            if (!empty($password)) {
                $hashedPass = md5($password);
                $sql = "UPDATE students SET
                            roll_number='$roll', full_name='$name', email='$email',
                            phone='$phone', course='$course', semester='$semester',
                            password='$hashedPass'
                        WHERE id=$id";
            } else {
                $sql = "UPDATE students SET
                            roll_number='$roll', full_name='$name', email='$email',
                            phone='$phone', course='$course', semester='$semester'
                        WHERE id=$id";
            }

            if (mysqli_query($conn, $sql)) {
                $msg = 'Student updated successfully!';
                $msgType = 'success';
                // Refresh student data
                $student = mysqli_fetch_assoc(
                    mysqli_query($conn, "SELECT * FROM students WHERE id=$id")
                );
            } else {
                $msg = 'Error: ' . mysqli_error($conn);
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
            <a href="students.php">Students</a> › Edit
        </div>
        <h1>Edit Student</h1>
        <p>Update information for <?php echo htmlspecialchars($student['full_name']); ?></p>
    </div>

    <?php if ($msg) echo "<div class='alert alert-$msgType'>$msg</div>"; ?>

    <div class="card">
        <div class="card-header">
            <h2>Student Information</h2>
            <span style="font-size:13px;color:#718096;">
                Roll: <strong><?php echo htmlspecialchars($student['roll_number']); ?></strong>
            </span>
        </div>

        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label>Roll Number *</label>
                    <input type="text" class="form-control" name="roll_number"
                           value="<?php echo htmlspecialchars($student['roll_number']); ?>"
                           style="text-transform:uppercase;" required>
                </div>

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" class="form-control" name="full_name"
                           value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" name="email"
                           value="<?php echo htmlspecialchars($student['email']); ?>">
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" name="phone"
                           value="<?php echo htmlspecialchars($student['phone']); ?>" maxlength="15">
                </div>

                <div class="form-group">
                    <label>Course *</label>
                    <select class="form-control" name="course" required>
                        <?php
                        $courses = ['BCA','MCA','BSc CS','BBA','BCom','BA'];
                        foreach ($courses as $c) {
                            $sel = ($c === $student['course']) ? 'selected' : '';
                            echo "<option value='$c' $sel>$c</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Semester *</label>
                    <select class="form-control" name="semester" required>
                        <?php for ($s = 1; $s <= 8; $s++): ?>
                            <option value="<?php echo $s; ?>"
                                <?php echo ($s == $student['semester']) ? 'selected' : ''; ?>>
                                Semester <?php echo $s; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="text" class="form-control" name="password"
                           placeholder="Leave blank to keep current password">
                    <small style="color:#718096;font-size:12px;">
                        Leave empty to keep existing password
                    </small>
                </div>
            </div>

            <div class="divider"></div>
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                <a href="students.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
