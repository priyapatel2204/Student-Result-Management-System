<?php
// ============================================================
// admin/results.php - View All Student Results
// ============================================================
session_start();
require_once '../config.php';
requireAdminLogin();

$pageTitle = 'Results';

// Filter by student (optional)
$studentFilter = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$yearFilter    = sanitize($conn, $_GET['year'] ?? date('Y'));
$search        = sanitize($conn, $_GET['search'] ?? '');

// Build query
$where = "WHERE 1=1";
if ($studentFilter) $where .= " AND st.id = $studentFilter";
if ($search)        $where .= " AND (st.roll_number LIKE '%$search%' OR st.full_name LIKE '%$search%')";

// Get distinct students who have results
$studentsWithResults = mysqli_query($conn,
    "SELECT DISTINCT st.id, st.roll_number, st.full_name, st.course, st.semester
     FROM students st
     JOIN results r ON st.id = r.student_id
     $where
     ORDER BY st.roll_number");

// Fetch all students for dropdown
$allStudents = mysqli_query($conn, "SELECT id, roll_number, full_name FROM students ORDER BY full_name");

include '../includes/header.php';
include '../includes/admin_nav.php';
?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="breadcrumb"><a href="dashboard.php">Dashboard</a> › Results</div>
        <h1>View Results</h1>
        <p>See result cards for all students</p>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div class="form-group" style="margin:0;flex:1;min-width:200px;">
                <label>Search Student</label>
                <input type="text" class="form-control" name="search"
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Name or Roll Number">
            </div>
            <div class="form-group" style="margin:0;min-width:160px;">
                <label>Filter by Student</label>
                <select class="form-control" name="student_id">
                    <option value="">All Students</option>
                    <?php while ($s = mysqli_fetch_assoc($allStudents)): ?>
                        <option value="<?php echo $s['id']; ?>"
                            <?php echo ($studentFilter == $s['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['full_name']); ?>
                            (<?php echo $s['roll_number']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="display:flex;gap:8px;padding-bottom:0;">
                <button type="submit" class="btn btn-primary">🔍 Filter</button>
                <a href="results.php" class="btn btn-outline">Reset</a>
            </div>
        </form>
    </div>

    <!-- Result Cards per Student -->
    <?php
    $studentCount = 0;
    while ($student = mysqli_fetch_assoc($studentsWithResults)):
        $studentCount++;
        $sid = $student['id'];

        // Fetch marks for this student
        $marksRes = mysqli_query($conn,
            "SELECT r.marks_obtained, s.subject_code, s.subject_name,
                    s.max_marks, s.pass_marks
             FROM results r
             JOIN subjects s ON r.subject_id = s.id
             WHERE r.student_id = $sid
             ORDER BY s.subject_code");

        $totalObtained = 0;
        $totalMax      = 0;
        $allPassed     = true;
        $subjectRows   = [];

        while ($m = mysqli_fetch_assoc($marksRes)) {
            $totalObtained += $m['marks_obtained'];
            $totalMax      += $m['max_marks'];
            if ($m['marks_obtained'] < $m['pass_marks']) $allPassed = false;
            $subjectRows[] = $m;
        }

        if (empty($subjectRows)) continue;

        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
        $grade      = calculateGrade($percentage);
        $gradeColor = getGradeColor($grade);
        $status     = $allPassed ? 'PASS' : 'FAIL';
        $statusClass = $allPassed ? 'badge-pass' : 'badge-fail';
    ?>

    <!-- Individual Result Card -->
    <div class="card" id="result-<?php echo $sid; ?>">
        <div class="card-header">
            <div>
                <h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
                <div style="font-size:13px;color:#718096;margin-top:2px;">
                    Roll: <strong><?php echo $student['roll_number']; ?></strong> &bull;
                    <?php echo $student['course']; ?> &bull;
                    Semester <?php echo $student['semester']; ?>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <!-- Grade Badge -->
                <div style="text-align:center;
                            background:<?php echo $gradeColor; ?>20;
                            border:2px solid <?php echo $gradeColor; ?>;
                            border-radius:10px;
                            padding:8px 16px;">
                    <div style="font-size:24px;font-weight:700;color:<?php echo $gradeColor; ?>;
                                font-family:'Playfair Display',serif;">
                        <?php echo $grade; ?>
                    </div>
                    <div style="font-size:11px;color:#718096;text-transform:uppercase;
                                letter-spacing:0.5px;">Grade</div>
                </div>
                <!-- Overall Status -->
                <span class="badge <?php echo $statusClass; ?>"
                      style="font-size:14px;padding:6px 14px;">
                    <?php echo $status; ?>
                </span>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Max Marks</th>
                        <th>Marks Obtained</th>
                        <th>Pass Marks</th>
                        <th>Status</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjectRows as $sub):
                        $subPct   = round(($sub['marks_obtained'] / $sub['max_marks']) * 100, 1);
                        $subGrade = calculateGrade($subPct);
                        $pass     = $sub['marks_obtained'] >= $sub['pass_marks'];
                    ?>
                    <tr>
                        <td><strong><?php echo $sub['subject_code']; ?></strong></td>
                        <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                        <td><?php echo $sub['max_marks']; ?></td>
                        <td>
                            <strong style="font-size:16px;"><?php echo $sub['marks_obtained']; ?></strong>
                        </td>
                        <td><?php echo $sub['pass_marks']; ?></td>
                        <td>
                            <span class="badge <?php echo $pass ? 'badge-pass' : 'badge-fail'; ?>">
                                <?php echo $pass ? 'PASS' : 'FAIL'; ?>
                            </span>
                        </td>
                        <td>
                            <span style="color:<?php echo getGradeColor($subGrade); ?>;font-weight:700;">
                                <?php echo $subGrade; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f7f9fd;font-weight:700;">
                        <td colspan="2"><strong>TOTAL</strong></td>
                        <td><?php echo $totalMax; ?></td>
                        <td><strong style="font-size:16px;"><?php echo $totalObtained; ?></strong></td>
                        <td colspan="2">
                            Percentage: <strong><?php echo $percentage; ?>%</strong>
                        </td>
                        <td style="color:<?php echo $gradeColor; ?>;font-size:18px;font-weight:800;">
                            <?php echo $grade; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
            <a href="marks.php?student_id=<?php echo $sid; ?>"
               class="btn btn-outline btn-sm">✏️ Edit Marks</a>
            <a href="../student/print_result.php?id=<?php echo $sid; ?>"
               class="btn btn-gold btn-sm" target="_blank">🖨️ Print Result</a>
        </div>
    </div>

    <?php endwhile; ?>

    <?php if ($studentCount === 0): ?>
    <div class="card" style="text-align:center;padding:50px;">
        <div style="font-size:48px;margin-bottom:12px;">📊</div>
        <h3 style="color:#718096;">No results found</h3>
        <p style="color:#a0aec0;margin-top:8px;">
            <?php echo $search ? "No match for \"$search\"" : 'No marks have been entered yet.'; ?>
        </p>
        <a href="marks.php" class="btn btn-primary" style="margin-top:16px;">Enter Marks →</a>
    </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
