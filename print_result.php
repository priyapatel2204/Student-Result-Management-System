<?php
// ============================================================
// student/print_result.php - Printable Result Card
// Accessible by both admin and student
// ============================================================
session_start();
require_once '../config.php';

// Get student ID
$sid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Access control: must be admin or the student themselves
if (!isAdminLoggedIn() && (!isStudentLoggedIn() || $_SESSION['student_id'] != $sid)) {
    redirect('../index.php?error=Unauthorized+access');
}

if ($sid === 0) {
    redirect('../index.php');
}

// Fetch student
$studentRes = mysqli_query($conn, "SELECT * FROM students WHERE id=$sid");
if (mysqli_num_rows($studentRes) === 0) {
    die("Student not found.");
}
$student = mysqli_fetch_assoc($studentRes);

// Fetch results
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

$percentage = ($totalMax > 0) ? round(($totalObtained / $totalMax) * 100, 2) : 0;
$grade      = calculateGrade($percentage);
$status     = $allPassed ? 'PASS' : 'FAIL';
$examYear   = !empty($subjectRows) ? $subjectRows[0]['exam_year'] : date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Card - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;500;600&display=swap');

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Source Sans 3', sans-serif;
            background: #f0f2f8;
            padding: 20px;
        }

        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 4px 30px rgba(0,0,0,0.12);
            border-radius: 12px;
            overflow: hidden;
        }

        /* Header */
        .result-header {
            background: #1a2744;
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
        }

        .result-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #c9a84c, #e4c97e, #c9a84c);
        }

        .college-name {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            margin-bottom: 4px;
        }

        .result-title {
            font-size: 14px;
            color: #c9a84c;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 8px;
        }

        /* Student Info */
        .student-section {
            padding: 24px 40px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
        }

        .student-details h2 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: #1a2744;
        }

        .student-details .info-row {
            display: flex;
            gap: 24px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .info-item {
            font-size: 13px;
        }

        .info-item span { color: #718096; }
        .info-item strong { color: #1a2744; }

        /* Result status badge */
        .result-badge {
            text-align: center;
            border: 2.5px solid;
            border-radius: 12px;
            padding: 14px 28px;
        }

        .result-badge.pass { border-color: #48bb78; background: #f0fff4; }
        .result-badge.fail { border-color: #fc8181; background: #fff5f5; }

        .result-badge .big-grade {
            font-family: 'Playfair Display', serif;
            font-size: 40px;
            font-weight: 700;
            line-height: 1;
        }

        .result-badge.pass .big-grade { color: #276749; }
        .result-badge.fail .big-grade { color: #c53030; }

        .result-badge .status-text {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .result-badge.pass .status-text { color: #276749; }
        .result-badge.fail .status-text { color: #c53030; }

        /* Marks Table */
        .marks-section { padding: 0 40px 20px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        thead th {
            background: #1a2744;
            color: white;
            padding: 11px 14px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:last-child { border-bottom: none; }

        tbody td { padding: 11px 14px; font-size: 14px; }

        tfoot td {
            padding: 12px 14px;
            background: #f7f9fd;
            font-weight: 700;
        }

        .badge-pass { background: #c6f6d5; color: #22543d; padding: 2px 10px; border-radius: 20px; font-size:12px; }
        .badge-fail { background: #fed7d7; color: #742a2a; padding: 2px 10px; border-radius: 20px; font-size:12px; }

        /* Summary */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border-top: 2px solid #1a2744;
            border-bottom: 2px solid #1a2744;
            margin: 0 40px 24px;
        }

        .summary-item {
            text-align: center;
            padding: 16px 10px;
            border-right: 1px solid #e2e8f0;
        }

        .summary-item:last-child { border-right: none; }

        .summary-value {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1a2744;
        }

        .summary-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #718096;
            margin-top: 4px;
        }

        /* Footer */
        .result-footer {
            padding: 20px 40px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #a0aec0;
        }

        .signature-line {
            text-align: center;
        }

        .signature-line .line {
            width: 150px;
            height: 1px;
            background: #a0aec0;
            margin: 0 auto 6px;
        }

        /* Print Buttons (hidden when printing) */
        .print-actions {
            text-align: center;
            padding: 20px;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            margin: 0 6px;
            display: inline-block;
            font-family: 'Source Sans 3', sans-serif;
        }

        .btn-navy { background: #1a2744; color: white; }
        .btn-outline { background: transparent; border: 1.5px solid #cbd5e0; color: #718096; }

        /* Print Styles */
        @media print {
            body { background: white; padding: 0; }
            .print-container { box-shadow: none; border-radius: 0; }
            .print-actions { display: none; }
        }
    </style>
</head>
<body>

<!-- Print / Download Buttons -->
<div class="print-actions">
    <button class="btn btn-navy" onclick="window.print()">🖨️ Print Result</button>
    <a href="javascript:history.back()" class="btn btn-outline">← Go Back</a>
</div>

<div class="print-container">

    <!-- Header -->
    <div class="result-header">
        <div style="font-size:40px;margin-bottom:8px;">🎓</div>
        <div class="college-name"><?php echo SITE_NAME; ?></div>
        <div class="result-title">Official Result Card</div>
    </div>

    <!-- Student Information -->
    <div class="student-section">
        <div class="student-details">
            <h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
            <div class="info-row">
                <div class="info-item">
                    <span>Roll Number: </span>
                    <strong><?php echo htmlspecialchars($student['roll_number']); ?></strong>
                </div>
                <div class="info-item">
                    <span>Course: </span>
                    <strong><?php echo htmlspecialchars($student['course']); ?></strong>
                </div>
                <div class="info-item">
                    <span>Semester: </span>
                    <strong><?php echo $student['semester']; ?></strong>
                </div>
                <div class="info-item">
                    <span>Exam Year: </span>
                    <strong><?php echo $examYear; ?></strong>
                </div>
            </div>
        </div>
        <div class="result-badge <?php echo $allPassed ? 'pass' : 'fail'; ?>">
            <div class="big-grade"><?php echo $grade; ?></div>
            <div class="status-text"><?php echo $status; ?></div>
            <div style="font-size:12px;color:#718096;margin-top:4px;"><?php echo $percentage; ?>%</div>
        </div>
    </div>

    <!-- Marks Table -->
    <div class="marks-section">
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
                    <td><strong style="font-size:16px;"><?php echo $sub['marks_obtained']; ?></strong></td>
                    <td><?php echo $sub['pass_marks']; ?></td>
                    <td>
                        <span class="<?php echo $pass ? 'badge-pass' : 'badge-fail'; ?>">
                            <?php echo $pass ? 'PASS' : 'FAIL'; ?>
                        </span>
                    </td>
                    <td><strong><?php echo $subGrade; ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>TOTAL</strong></td>
                    <td><strong><?php echo $totalMax; ?></strong></td>
                    <td><strong style="font-size:16px;"><?php echo $totalObtained; ?></strong></td>
                    <td colspan="2">Percentage: <strong><?php echo $percentage; ?>%</strong></td>
                    <td><strong style="font-size:18px;"><?php echo $grade; ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Summary Grid -->
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-value"><?php echo $totalObtained; ?></div>
            <div class="summary-label">Marks Obtained</div>
        </div>
        <div class="summary-item">
            <div class="summary-value"><?php echo $totalMax; ?></div>
            <div class="summary-label">Total Marks</div>
        </div>
        <div class="summary-item">
            <div class="summary-value"><?php echo $percentage; ?>%</div>
            <div class="summary-label">Percentage</div>
        </div>
        <div class="summary-item">
            <div class="summary-value"><?php echo $grade; ?></div>
            <div class="summary-label">Grade</div>
        </div>
    </div>

    <!-- Footer Signatures -->
    <div class="result-footer">
        <div>
            <div>Generated: <?php echo date('d M Y, h:i A'); ?></div>
            <div>This is a computer generated result card.</div>
        </div>
        <div style="display:flex;gap:60px;">
            <div class="signature-line">
                <div class="line"></div>
                <div>Exam Controller</div>
            </div>
            <div class="signature-line">
                <div class="line"></div>
                <div>Principal</div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
