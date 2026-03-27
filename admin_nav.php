<?php
// Admin navigation bar - included on all admin pages
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <div class="logo-icon">🎓</div>
        <div>
            <div class="brand-text">ResultMS</div>
            <div class="brand-sub">Admin Panel</div>
        </div>
    </a>

    <div class="navbar-nav">
        <a href="dashboard.php"
           class="nav-link <?php echo $current === 'dashboard.php' ? 'active' : ''; ?>">
            <span>⊞</span> <span class="label">Dashboard</span>
        </a>
        <a href="students.php"
           class="nav-link <?php echo $current === 'students.php' ? 'active' : ''; ?>">
            <span>👤</span> <span class="label">Students</span>
        </a>
        <a href="subjects.php"
           class="nav-link <?php echo $current === 'subjects.php' ? 'active' : ''; ?>">
            <span>📚</span> <span class="label">Subjects</span>
        </a>
        <a href="marks.php"
           class="nav-link <?php echo $current === 'marks.php' ? 'active' : ''; ?>">
            <span>✏️</span> <span class="label">Marks</span>
        </a>
        <a href="results.php"
           class="nav-link <?php echo $current === 'results.php' ? 'active' : ''; ?>">
            <span>📊</span> <span class="label">Results</span>
        </a>
        <a href="logout.php" class="nav-link logout">
            <span>⇤</span> <span class="label">Logout</span>
        </a>
    </div>
</nav>
