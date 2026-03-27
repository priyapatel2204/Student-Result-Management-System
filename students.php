<?php
// ============================================================
// admin/students.php - View, Search & Delete Students
// ============================================================
session_start();
require_once '../config.php';
requireAdminLogin();

$pageTitle = 'Students';
$msg = $msgType = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM students WHERE id=$id");
    $msg     = 'Student deleted successfully.';
    $msgType = 'success';
}

// Search
$search = '';
$where  = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($conn, $_GET['search']);
    $where  = "WHERE roll_number LIKE '%$search%' OR full_name LIKE '%$search%'";
}

$students = mysqli_query($conn, "SELECT * FROM students $where ORDER BY created_at DESC");

include '../includes/header.php';
include '../includes/admin_nav.php';
?>

<div class="page-wrapper">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> › Students
        </div>
        <h1>Student Records</h1>
        <p>Manage all enrolled students</p>
    </div>

    <?php if ($msg) echo "<div class='alert alert-$msgType'>$msg</div>"; ?>

    <div class="card">
        <div class="card-header">
            <h2>All Students</h2>
            <a href="add_student.php" class="btn btn-primary btn-sm">➕ Add Student</a>
        </div>

        <!-- Search Bar -->
        <form method="GET" class="search-bar">
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search by name or roll number..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">🔍 Search</button>
            <?php if ($search): ?>
                <a href="students.php" class="btn btn-outline">✕ Clear</a>
            <?php endif; ?>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Roll Number</th>
                        <th>Full Name</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $count = 0;
                    while ($row = mysqli_fetch_assoc($students)):
                        $count++;
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['roll_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td>Sem <?php echo $row['semester']; ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="edit_student.php?id=<?php echo $row['id']; ?>"
                                   class="btn btn-outline btn-sm">✏️</a>
                                <a href="results.php?student_id=<?php echo $row['id']; ?>"
                                   class="btn btn-gold btn-sm">📊</a>
                                <a href="students.php?delete=<?php echo $row['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this student and all their results?')">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($count === 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;color:#718096;padding:30px;">
                            <?php echo $search ? "No students found for \"$search\"." : 'No students yet.'; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
