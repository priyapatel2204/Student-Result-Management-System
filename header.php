<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <style>
        /* ============================================================
           Global CSS - Shared across all pages
           Aesthetic: Academic/Institutional with modern touches
           Color Palette: Deep navy + gold + clean whites
        ============================================================ */

        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --navy:    #1a2744;
            --navy-lt: #243460;
            --gold:    #c9a84c;
            --gold-lt: #e4c97e;
            --white:   #ffffff;
            --bg:      #f0f2f8;
            --card:    #ffffff;
            --text:    #2d3748;
            --muted:   #718096;
            --border:  #e2e8f0;
            --success: #276749;
            --danger:  #c53030;
            --warning: #d97706;
            --info:    #2b6cb0;
            --radius:  10px;
            --shadow:  0 4px 20px rgba(26,39,68,0.10);
        }

        body {
            font-family: 'Source Sans 3', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ---- Navigation Bar ---- */
        .navbar {
            background: var(--navy);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.25);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar-brand .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--gold);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .navbar-brand .brand-text {
            font-family: 'Playfair Display', serif;
            color: var(--white);
            font-size: 18px;
            line-height: 1.2;
        }

        .navbar-brand .brand-sub {
            color: var(--gold-lt);
            font-family: 'Source Sans 3', sans-serif;
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.12);
            color: var(--white);
        }

        .nav-link.logout {
            background: rgba(201,168,76,0.2);
            color: var(--gold-lt);
            border: 1px solid rgba(201,168,76,0.3);
        }

        .nav-link.logout:hover {
            background: var(--gold);
            color: var(--navy);
        }

        /* ---- Page Wrapper ---- */
        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* ---- Page Header ---- */
        .page-header {
            margin-bottom: 28px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--navy);
            margin-bottom: 4px;
        }

        .page-header p {
            color: var(--muted);
            font-size: 15px;
        }

        .page-header .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .page-header .breadcrumb a {
            color: var(--gold);
            text-decoration: none;
        }

        /* ---- Cards ---- */
        .card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 28px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--bg);
        }

        .card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: var(--navy);
        }

        /* ---- Stats Cards ---- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(26,39,68,0.15);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .stat-icon.navy  { background: rgba(26,39,68,0.1); }
        .stat-icon.gold  { background: rgba(201,168,76,0.15); }
        .stat-icon.green { background: rgba(39,103,73,0.1); }
        .stat-icon.red   { background: rgba(197,48,48,0.1); }

        .stat-info .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1;
            font-family: 'Playfair Display', serif;
        }

        .stat-info .stat-label {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ---- Forms ---- */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: var(--navy);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 15px;
            color: var(--text);
            background: #fafbfd;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(26,39,68,0.08);
            background: var(--white);
        }

        .form-control.error {
            border-color: var(--danger);
        }

        .error-msg {
            color: var(--danger);
            font-size: 12px;
            margin-top: 4px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
        }

        /* ---- Buttons ---- */
        .btn {
            padding: 10px 22px;
            border: none;
            border-radius: 8px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--navy);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--navy-lt);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,39,68,0.25);
        }

        .btn-gold {
            background: var(--gold);
            color: var(--navy);
        }

        .btn-gold:hover {
            background: var(--gold-lt);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #9b2c2c;
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text);
        }

        .btn-outline:hover {
            border-color: var(--navy);
            color: var(--navy);
            background: rgba(26,39,68,0.04);
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 13px;
        }

        /* ---- Tables ---- */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: var(--navy);
            color: var(--white);
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        thead th:first-child { border-radius: 8px 0 0 0; }
        thead th:last-child  { border-radius: 0 8px 0 0; }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        tbody tr:hover { background: #f7f9fd; }

        tbody td {
            padding: 13px 16px;
            font-size: 14px;
            color: var(--text);
        }

        /* ---- Badges ---- */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pass    { background: #c6f6d5; color: #22543d; }
        .badge-fail    { background: #fed7d7; color: #742a2a; }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-warning { background: #fefcbf; color: #744210; }

        /* ---- Alert Messages ---- */
        .alert {
            padding: 13px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success { background: #f0fff4; color: #22543d; border-left: 4px solid #48bb78; }
        .alert-danger  { background: #fff5f5; color: #742a2a; border-left: 4px solid #fc8181; }
        .alert-warning { background: #fffbeb; color: #744210; border-left: 4px solid #f6ad55; }
        .alert-info    { background: #ebf8ff; color: #2a4365; border-left: 4px solid #63b3ed; }

        /* ---- Search bar ---- */
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }

        .search-bar .form-control {
            max-width: 320px;
        }

        /* ---- Action buttons in table ---- */
        .action-btns {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* ---- Footer ---- */
        .page-footer {
            text-align: center;
            padding: 20px;
            color: var(--muted);
            font-size: 13px;
            border-top: 1px solid var(--border);
            margin-top: 40px;
        }

        /* ---- Divider ---- */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 20px 0;
        }

        /* ---- Responsive ---- */
        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .page-wrapper { padding: 20px 14px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .navbar-brand .brand-text { font-size: 15px; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .nav-link span.label { display: none; }
        }
    </style>
</head>
<body>
