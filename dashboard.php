<?php
// ============================================================
// admin/dashboard.php - Admin Dashboard
// ============================================================
session_start();
require_once '../config.php';
requireAdminLogin();

$pageTitle = 'Dashboard';

// Fetch statistics
$totalStudents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students"))['c'];
$totalSubjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM subjects"))['c'];
$totalResults  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM results"))['c'];

// Count pass/fail: pass = marks >= pass_marks
$passCount = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(DISTINCT r.student_id) as c FROM results r
     JOIN subjects s ON r.subject_id = s.id
     WHERE r.marks_obtained >= s.pass_marks"))['c'];

// Recent students
$recentStudents = mysqli_query($conn,
    "SELECT * FROM students ORDER BY created_at DESC LIMIT 5");

include '../includes/header.php';
include '../includes/admin_nav.php';
?>

<div class="page-wrapper">

    <!-- Page Header -->
    <div class="page-header">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?> 👋</h1>
        <p>Here's an overview of the Student Result Management System</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon navy">👥</div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $totalStudents; ?></div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold">📚</div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $totalSubjects; ?></div>
                <div class="stat-label">Subjects</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">📊</div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $totalResults; ?></div>
                <div class="stat-label">Result Entries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">✅</div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $passCount; ?></div>
                <div class="stat-label">Students Passed</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2>Quick Actions</h2>
        </div>
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
            <a href="add_student.php" class="btn btn-primary">➕ Add Student</a>
            <a href="subjects.php"    class="btn btn-gold">📚 Manage Subjects</a>
            <a href="marks.php"       class="btn btn-success">✏️ Enter Marks</a>
            <a href="results.php"     class="btn btn-outline">📊 View All Results</a>
        </div>
    </div>

    <!-- Recent Students -->
    <div class="card">
        <div class="card-header">
            <h2>Recently Added Students</h2>
            <a href="students.php" class="btn btn-outline btn-sm">View All →</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Roll Number</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    while ($row = mysqli_fetch_assoc($recentStudents)):
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['roll_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td>Sem <?php echo $row['semester']; ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="edit_student.php?id=<?php echo $row['id']; ?>"
                                   class="btn btn-outline btn-sm">✏️ Edit</a>
                                <a href="results.php?student_id=<?php echo $row['id']; ?>"
                                   class="btn btn-gold btn-sm">📊 Result</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($totalStudents === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:#718096;padding:30px;">
                            No students added yet. <a href="add_student.php">Add one now →</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
