<?php
// ============================================================
// admin/subjects.php - Manage Subjects (Add / Edit / Delete)
// ============================================================
session_start();
require_once '../config.php';
requireAdminLogin();

$pageTitle = 'Subjects';
$msg = $msgType = '';
$editing = null;

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM subjects WHERE id=$id");
    $msg = 'Subject deleted.';
    $msgType = 'success';
}

// Load subject for editing
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE id=$id");
    $editing = mysqli_fetch_assoc($res);
}

// Handle Add or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subId   = (int)($_POST['sub_id'] ?? 0);
    $code    = strtoupper(sanitize($conn, $_POST['subject_code']));
    $name    = sanitize($conn, $_POST['subject_name']);
    $max     = (int)$_POST['max_marks'];
    $pass    = (int)$_POST['pass_marks'];
    $sem     = (int)$_POST['semester'];
    $course  = sanitize($conn, $_POST['course']);

    if (empty($code) || empty($name) || empty($course)) {
        $msg = 'Code, Name, and Course are required.';
        $msgType = 'danger';
    } elseif ($pass > $max) {
        $msg = 'Pass marks cannot be greater than max marks.';
        $msgType = 'danger';
    } else {
        if ($subId > 0) {
            // Update existing
            $sql = "UPDATE subjects SET subject_code='$code', subject_name='$name',
                        max_marks=$max, pass_marks=$pass, semester=$sem, course='$course'
                    WHERE id=$subId";
            if (mysqli_query($conn, $sql)) {
                $msg = 'Subject updated successfully!';
                $msgType = 'success';
                $editing = null;
            } else {
                $msg = 'Error: ' . mysqli_error($conn);
                $msgType = 'danger';
            }
        } else {
            // Check duplicate code
            $check = mysqli_query($conn, "SELECT id FROM subjects WHERE subject_code='$code'");
            if (mysqli_num_rows($check) > 0) {
                $msg = "Subject code '$code' already exists.";
                $msgType = 'danger';
            } else {
                $sql = "INSERT INTO subjects (subject_code, subject_name, max_marks, pass_marks, semester, course)
                        VALUES ('$code','$name',$max,$pass,$sem,'$course')";
                if (mysqli_query($conn, $sql)) {
                    $msg = "Subject '$name' added!";
                    $msgType = 'success';
                } else {
                    $msg = 'Error: ' . mysqli_error($conn);
                    $msgType = 'danger';
                }
            }
        }
    }
}

$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY course, semester, subject_code");

include '../includes/header.php';
include '../includes/admin_nav.php';
?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="breadcrumb"><a href="dashboard.php">Dashboard</a> › Subjects</div>
        <h1>Manage Subjects</h1>
        <p>Add, edit, or remove subjects from the curriculum</p>
    </div>

    <?php if ($msg) echo "<div class='alert alert-$msgType'>$msg</div>"; ?>

    <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:start;">

        <!-- Add / Edit Form -->
        <div class="card">
            <div class="card-header">
                <h2><?php echo $editing ? '✏️ Edit Subject' : '➕ Add Subject'; ?></h2>
                <?php if ($editing): ?>
                    <a href="subjects.php" class="btn btn-outline btn-sm">✕ Cancel</a>
                <?php endif; ?>
            </div>

            <form method="POST" action="">
                <?php if ($editing): ?>
                    <input type="hidden" name="sub_id" value="<?php echo $editing['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Subject Code *</label>
                    <input type="text" class="form-control" name="subject_code"
                           value="<?php echo $editing ? htmlspecialchars($editing['subject_code']) : ''; ?>"
                           placeholder="e.g. BCA201" style="text-transform:uppercase;" required>
                </div>

                <div class="form-group">
                    <label>Subject Name *</label>
                    <input type="text" class="form-control" name="subject_name"
                           value="<?php echo $editing ? htmlspecialchars($editing['subject_name']) : ''; ?>"
                           placeholder="e.g. Data Structures" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Max Marks</label>
                        <input type="number" class="form-control" name="max_marks"
                               value="<?php echo $editing ? $editing['max_marks'] : 100; ?>"
                               min="1" max="200" required>
                    </div>
                    <div class="form-group">
                        <label>Pass Marks</label>
                        <input type="number" class="form-control" name="pass_marks"
                               value="<?php echo $editing ? $editing['pass_marks'] : 35; ?>"
                               min="1" max="200" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Course *</label>
                    <select class="form-control" name="course" required>
                        <?php
                        $courses = ['BCA','MCA','BSc CS','BBA','BCom','BA'];
                        foreach ($courses as $c) {
                            $sel = ($editing && $editing['course'] === $c) ? 'selected' : '';
                            echo "<option value='$c' $sel>$c</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Semester</label>
                    <select class="form-control" name="semester" required>
                        <?php for ($s = 1; $s <= 8; $s++): ?>
                            <option value="<?php echo $s; ?>"
                                <?php echo ($editing && $editing['semester'] == $s) ? 'selected' : ''; ?>>
                                Semester <?php echo $s; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <?php echo $editing ? '💾 Update Subject' : '✅ Add Subject'; ?>
                </button>
            </form>
        </div>

        <!-- Subjects List -->
        <div class="card">
            <div class="card-header">
                <h2>All Subjects</h2>
                <span style="font-size:13px;color:#718096;">
                    <?php echo mysqli_num_rows(mysqli_query($conn,"SELECT id FROM subjects")); ?> total
                </span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Subject</th>
                            <th>Course/Sem</th>
                            <th>Marks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 0;
                        while ($row = mysqli_fetch_assoc($subjects)):
                            $count++;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['subject_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td><?php echo $row['course']; ?> &bull; S<?php echo $row['semester']; ?></td>
                            <td>
                                <?php echo $row['pass_marks']; ?>/<?php echo $row['max_marks']; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="subjects.php?edit=<?php echo $row['id']; ?>"
                                       class="btn btn-outline btn-sm">✏️</a>
                                    <a href="subjects.php?delete=<?php echo $row['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Delete this subject?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($count === 0): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:#718096;padding:20px;">
                                No subjects added yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
