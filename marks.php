<?php
// ============================================================
// admin/marks.php - Enter / Update Student Marks
// ============================================================
session_start();
require_once '../config.php';
requireAdminLogin();

$pageTitle = 'Enter Marks';
$msg = $msgType = '';

// Fetch students and subjects for dropdowns
$students = mysqli_query($conn, "SELECT id, roll_number, full_name, course, semester FROM students ORDER BY full_name");
$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY course, semester, subject_name");

// Step 1: Student selected - load their subjects
$selectedStudent = null;
$studentSubjects = [];
$existingMarks   = [];

if (isset($_GET['student_id']) && !empty($_GET['student_id'])) {
    $sid = (int)$_GET['student_id'];
    $res = mysqli_query($conn, "SELECT * FROM students WHERE id=$sid");
    if (mysqli_num_rows($res) > 0) {
        $selectedStudent = mysqli_fetch_assoc($res);
        // Load subjects for this student's course and semester
        $studentSubjects = mysqli_query($conn,
            "SELECT * FROM subjects
             WHERE course='{$selectedStudent['course']}' AND semester={$selectedStudent['semester']}
             ORDER BY subject_name");
        // Load existing marks
        $marksRes = mysqli_query($conn,
            "SELECT r.*, s.subject_name FROM results r
             JOIN subjects s ON r.subject_id = s.id
             WHERE r.student_id=$sid");
        while ($m = mysqli_fetch_assoc($marksRes)) {
            $existingMarks[$m['subject_id']] = $m['marks_obtained'];
        }
    }
}

// Step 2: Save marks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $sid  = (int)$_POST['student_id'];
    $year = sanitize($conn, $_POST['exam_year']);
    $marksData = $_POST['marks'] ?? [];

    $saved  = 0;
    $errors = 0;

    foreach ($marksData as $subId => $marks) {
        $subId = (int)$subId;
        $marks = max(0, (int)$marks);

        // Get max marks for this subject
        $subRes  = mysqli_query($conn, "SELECT max_marks FROM subjects WHERE id=$subId");
        $subRow  = mysqli_fetch_assoc($subRes);
        $maxMarks = $subRow['max_marks'];

        if ($marks > $maxMarks) {
            $errors++;
            continue;
        }

        // Insert or Update
        $sql = "INSERT INTO results (student_id, subject_id, marks_obtained, exam_year)
                VALUES ($sid, $subId, $marks, '$year')
                ON DUPLICATE KEY UPDATE marks_obtained=$marks, exam_year='$year'";
        if (mysqli_query($conn, $sql)) $saved++;
        else $errors++;
    }

    if ($errors === 0) {
        $msg = "Marks saved for $saved subject(s)!";
        $msgType = 'success';
    } else {
        $msg = "Saved $saved, but $errors had errors (marks exceeded max).";
        $msgType = 'warning';
    }

    // Refresh existing marks
    if ($selectedStudent) {
        $marksRes = mysqli_query($conn,
            "SELECT r.*, s.subject_name FROM results r
             JOIN subjects s ON r.subject_id = s.id
             WHERE r.student_id=$sid");
        $existingMarks = [];
        while ($m = mysqli_fetch_assoc($marksRes)) {
            $existingMarks[$m['subject_id']] = $m['marks_obtained'];
        }
    }
}

include '../includes/header.php';
include '../includes/admin_nav.php';
?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="breadcrumb"><a href="dashboard.php">Dashboard</a> › Enter Marks</div>
        <h1>Enter / Update Marks</h1>
        <p>Select a student to enter or update their subject marks</p>
    </div>

    <?php if ($msg) echo "<div class='alert alert-$msgType'>$msg</div>"; ?>

    <!-- Step 1: Select Student -->
    <div class="card">
        <div class="card-header">
            <h2>Step 1: Select Student</h2>
        </div>
        <form method="GET" action="" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div class="form-group" style="flex:1;min-width:220px;margin:0;">
                <label>Select Student</label>
                <select class="form-control" name="student_id" onchange="this.form.submit()">
                    <option value="">-- Choose a Student --</option>
                    <?php
                    while ($s = mysqli_fetch_assoc($students)) {
                        $sel = ($selectedStudent && $selectedStudent['id'] == $s['id']) ? 'selected' : '';
                        echo "<option value='{$s['id']}' $sel>
                                {$s['roll_number']} - " . htmlspecialchars($s['full_name']) . "
                                ({$s['course']}, Sem {$s['semester']})
                              </option>";
                    }
                    ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Step 2: Enter Marks -->
    <?php if ($selectedStudent): ?>
    <div class="card">
        <div class="card-header">
            <h2>Step 2: Enter Marks</h2>
            <div>
                <strong><?php echo htmlspecialchars($selectedStudent['full_name']); ?></strong>
                <span style="color:#718096;font-size:13px;margin-left:10px;">
                    <?php echo $selectedStudent['roll_number']; ?> &bull;
                    <?php echo $selectedStudent['course']; ?> Sem <?php echo $selectedStudent['semester']; ?>
                </span>
            </div>
        </div>

        <?php
        $subjectCount = mysqli_num_rows($studentSubjects);
        if ($subjectCount === 0):
        ?>
        <div class="alert alert-warning">
            ⚠️ No subjects found for <?php echo $selectedStudent['course']; ?>
            Semester <?php echo $selectedStudent['semester']; ?>.
            <a href="subjects.php">Add subjects first →</a>
        </div>
        <?php else: ?>

        <form method="POST" action="?student_id=<?php echo $selectedStudent['id']; ?>"
              onsubmit="return validateMarks()">
            <input type="hidden" name="save_marks" value="1">
            <input type="hidden" name="student_id" value="<?php echo $selectedStudent['id']; ?>">

            <div class="form-group" style="max-width:200px;">
                <label>Exam Year</label>
                <input type="text" class="form-control" name="exam_year"
                       value="<?php echo date('Y'); ?>" placeholder="e.g. 2024" required>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Max Marks</th>
                            <th>Pass Marks</th>
                            <th>Marks Obtained *</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="marksTable">
                        <?php
                        $i = 1;
                        while ($sub = mysqli_fetch_assoc($studentSubjects)):
                            $existing = $existingMarks[$sub['id']] ?? '';
                            $status   = '';
                            if ($existing !== '') {
                                $status = ($existing >= $sub['pass_marks'])
                                    ? "<span class='badge badge-pass'>PASS</span>"
                                    : "<span class='badge badge-fail'>FAIL</span>";
                            }
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($sub['subject_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                            <td><?php echo $sub['max_marks']; ?></td>
                            <td><?php echo $sub['pass_marks']; ?></td>
                            <td>
                                <input type="number"
                                       class="form-control marks-input"
                                       name="marks[<?php echo $sub['id']; ?>]"
                                       value="<?php echo $existing; ?>"
                                       min="0"
                                       max="<?php echo $sub['max_marks']; ?>"
                                       placeholder="0 - <?php echo $sub['max_marks']; ?>"
                                       style="width:120px;"
                                       data-max="<?php echo $sub['max_marks']; ?>"
                                       data-pass="<?php echo $sub['pass_marks']; ?>"
                                       data-row="<?php echo $i-1; ?>"
                                       oninput="updateStatus(this)">
                            </td>
                            <td id="status-<?php echo $i-1; ?>">
                                <?php echo $status; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="divider"></div>
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary">💾 Save All Marks</button>
                <a href="results.php?student_id=<?php echo $selectedStudent['id']; ?>"
                   class="btn btn-gold">📊 View Result</a>
            </div>
        </form>

        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Live status update as marks are typed
function updateStatus(input) {
    const val  = parseInt(input.value);
    const max  = parseInt(input.dataset.max);
    const pass = parseInt(input.dataset.pass);
    const row  = input.dataset.row;
    const cell = document.getElementById('status-' + row);

    // Validate range
    if (val > max) {
        input.style.borderColor = '#c53030';
        cell.innerHTML = "<span style='color:#c53030;font-size:12px;'>Exceeds max!</span>";
        return;
    }

    input.style.borderColor = '';

    if (!isNaN(val)) {
        if (val >= pass) {
            cell.innerHTML = "<span class='badge badge-pass'>PASS</span>";
        } else {
            cell.innerHTML = "<span class='badge badge-fail'>FAIL</span>";
        }
    } else {
        cell.innerHTML = '';
    }
}

// Validate before submit
function validateMarks() {
    const inputs = document.querySelectorAll('.marks-input');
    for (let inp of inputs) {
        const val = parseInt(inp.value);
        const max = parseInt(inp.dataset.max);
        if (inp.value !== '' && (isNaN(val) || val < 0 || val > max)) {
            alert('One or more marks are invalid. Please check values.');
            inp.focus();
            return false;
        }
    }
    return true;
}
</script>

<?php include '../includes/footer.php'; ?>
