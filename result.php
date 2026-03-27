<?php
// ============================================================
// student/result.php - Student View Their Own Result
// ============================================================
session_start();
require_once '../config.php';
requireStudentLogin();

$pageTitle = 'My Result';
$sid       = $_SESSION['student_id'];

// Fetch student info
$studentRes = mysqli_query($conn, "SELECT * FROM students WHERE id=$sid");
$student    = mysqli_fetch_assoc($studentRes);

// Fetch results with subject details
$marksRes = mysqli_query($conn,
    "SELECT r.marks_obtained, r.exam_year, s.subject_code, s.subject_name,
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

$hasResult  = !empty($subjectRows);
$percentage = ($hasResult && $totalMax > 0) ? round(($totalObtained / $totalMax) * 100, 2) : 0;
$grade      = $hasResult ? calculateGrade($percentage) : '-';
$gradeColor = $hasResult ? getGradeColor($grade) : '#718096';
$status     = $allPassed ? 'PASS' : 'FAIL';
$examYear   = $hasResult ? $subjectRows[0]['exam_year'] : '';

include '../includes/header.php';
?>

<!-- Student-specific navbar -->
<nav class="navbar">
    <div class="navbar-brand">
        <div class="logo-icon">🎓</div>
        <div>
            <div class="brand-text">ResultMS</div>
            <div class="brand-sub">Student Portal</div>
        </div>
    </div>
    <div class="navbar-nav">
        <span class="nav-link" style="cursor:default;">
            👤 <?php echo htmlspecialchars($_SESSION['student_name']); ?>
        </span>
        <a href="../admin/logout.php" class="nav-link logout">⇤ Logout</a>
    </div>
</nav>

<div class="page-wrapper">
    <!-- Welcome -->
    <div class="page-header">
        <h1>My Result Card</h1>
        <p><?php echo htmlspecialchars($student['course']); ?> —
           Semester <?php echo $student['semester']; ?>
           <?php if ($examYear) echo "— Exam Year $examYear"; ?>
        </p>
    </div>

    <!-- Student Info Card -->
    <div class="card">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
            <!-- Avatar -->
            <div style="width:70px;height:70px;background:var(--navy);border-radius:50%;
                        display:flex;align-items:center;justify-content:center;
                        font-size:28px;flex-shrink:0;">🎓</div>
            <!-- Info -->
            <div style="flex:1;">
                <h2 style="font-family:'Playfair Display',serif;font-size:22px;color:var(--navy);">
                    <?php echo htmlspecialchars($student['full_name']); ?>
                </h2>
                <div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:8px;font-size:14px;color:#718096;">
                    <span>📋 Roll: <strong style="color:var(--navy);"><?php echo $student['roll_number']; ?></strong></span>
                    <span>📚 Course: <strong style="color:var(--navy);"><?php echo $student['course']; ?></strong></span>
                    <span>🗓️ Semester: <strong style="color:var(--navy);">Sem <?php echo $student['semester']; ?></strong></span>
                    <?php if ($student['email']): ?>
                    <span>✉️ <?php echo htmlspecialchars($student['email']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Grade + Status (if has result) -->
            <?php if ($hasResult): ?>
            <div style="display:flex;gap:14px;align-items:center;">
                <div style="text-align:center;
                            background:<?php echo $gradeColor; ?>18;
                            border:2px solid <?php echo $gradeColor; ?>;
                            border-radius:12px;padding:12px 22px;">
                    <div style="font-size:36px;font-weight:800;color:<?php echo $gradeColor; ?>;
                                font-family:'Playfair Display',serif;line-height:1;">
                        <?php echo $grade; ?>
                    </div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;
                                color:#718096;margin-top:4px;">Grade</div>
                </div>
                <span class="badge <?php echo $allPassed ? 'badge-pass' : 'badge-fail'; ?>"
                      style="font-size:16px;padding:8px 20px;">
                    <?php echo $status; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$hasResult): ?>
    <!-- No results yet -->
    <div class="card" style="text-align:center;padding:60px 20px;">
        <div style="font-size:60px;margin-bottom:16px;">📋</div>
        <h3 style="color:#718096;">Results Not Published Yet</h3>
        <p style="color:#a0aec0;margin-top:8px;">
            Your results have not been entered by the admin yet. Please check back later.
        </p>
    </div>

    <?php else: ?>
    <!-- Results Table -->
    <div class="card">
        <div class="card-header">
            <h2>Subject-wise Marks</h2>
            <a href="print_result.php?id=<?php echo $sid; ?>" target="_blank"
               class="btn btn-gold btn-sm">🖨️ Print / Download</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
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
                    <?php
                    $i = 1;
                    foreach ($subjectRows as $sub):
                        $subPct   = round(($sub['marks_obtained'] / $sub['max_marks']) * 100, 1);
                        $subGrade = calculateGrade($subPct);
                        $pass     = $sub['marks_obtained'] >= $sub['pass_marks'];
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($sub['subject_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                        <td><?php echo $sub['max_marks']; ?></td>
                        <td>
                            <span style="font-size:18px;font-weight:700;color:var(--navy);">
                                <?php echo $sub['marks_obtained']; ?>
                            </span>
                        </td>
                        <td><?php echo $sub['pass_marks']; ?></td>
                        <td>
                            <span class="badge <?php echo $pass ? 'badge-pass' : 'badge-fail'; ?>">
                                <?php echo $pass ? 'PASS' : 'FAIL'; ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color:<?php echo getGradeColor($subGrade); ?>;font-size:16px;">
                                <?php echo $subGrade; ?>
                            </strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f0f2f8;">
                        <td colspan="3"><strong>GRAND TOTAL</strong></td>
                        <td><strong><?php echo $totalMax; ?></strong></td>
                        <td><strong style="font-size:18px;color:var(--navy);"><?php echo $totalObtained; ?></strong></td>
                        <td colspan="2">
                            Percentage: <strong style="font-size:16px;"><?php echo $percentage; ?>%</strong>
                        </td>
                        <td style="color:<?php echo $gradeColor; ?>;font-size:22px;font-weight:800;">
                            <?php echo $grade; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Summary Stats -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin-top:24px;">
            <div style="background:#f7f9fd;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:28px;font-weight:800;color:var(--navy);font-family:'Playfair Display',serif;">
                    <?php echo $totalObtained; ?>/<?php echo $totalMax; ?>
                </div>
                <div style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px;">
                    Total Marks
                </div>
            </div>
            <div style="background:#f7f9fd;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:28px;font-weight:800;color:var(--navy);font-family:'Playfair Display',serif;">
                    <?php echo $percentage; ?>%
                </div>
                <div style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px;">
                    Percentage
                </div>
            </div>
            <div style="background:<?php echo $gradeColor; ?>15;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:28px;font-weight:800;color:<?php echo $gradeColor; ?>;font-family:'Playfair Display',serif;">
                    <?php echo $grade; ?>
                </div>
                <div style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px;">
                    Overall Grade
                </div>
            </div>
            <div style="background:<?php echo $allPassed ? '#c6f6d5' : '#fed7d7'; ?>;border-radius:10px;padding:16px;text-align:center;">
                <div style="font-size:28px;font-weight:800;color:<?php echo $allPassed ? '#22543d' : '#742a2a'; ?>;font-family:'Playfair Display',serif;">
                    <?php echo $status; ?>
                </div>
                <div style="font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px;">
                    Result Status
                </div>
            </div>
        </div>

        <div style="margin-top:20px;">
            <a href="print_result.php?id=<?php echo $sid; ?>"
               target="_blank" class="btn btn-gold">🖨️ Print / Download Result</a>
        </div>
    </div>

    <!-- Grade Scale Reference -->
    <div class="card">
        <div class="card-header"><h2>Grade Scale Reference</h2></div>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php
            $gradeScale = [
                'A+' => '90-100%', 'A' => '80-89%', 'B+' => '70-79%',
                'B'  => '60-69%',  'C' => '50-59%', 'D'  => '40-49%',
                'F'  => 'Below 40%'
            ];
            foreach ($gradeScale as $g => $range):
            ?>
            <div style="background:<?php echo getGradeColor($g); ?>15;
                        border:1.5px solid <?php echo getGradeColor($g); ?>;
                        border-radius:8px;padding:10px 18px;text-align:center;">
                <strong style="color:<?php echo getGradeColor($g); ?>;font-size:18px;"><?php echo $g; ?></strong>
                <div style="font-size:12px;color:#718096;"><?php echo $range; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
