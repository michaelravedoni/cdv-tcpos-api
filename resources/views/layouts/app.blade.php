<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Château de Villa — Sync Dashboard')</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root {
            --bg: #f4f5f7;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-muted: #64748b;
            --primary: #7c2d12;
            --primary-hover: #5c1f0b;
            --primary-soft: #fef2f2;
            --success: #15803d;
            --success-bg: #dcfce7;
            --warning: #b45309;
            --warning-bg: #fef3c7;
            --danger: #b91c1c;
            --danger-bg: #fee2e2;
            --info: #1d4ed8;
            --info-bg: #dbeafe;
            --radius: 10px;
            --shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12.5px; }

        /* Topbar */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .topbar-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 15px;
            color: var(--text);
            white-space: nowrap;
        }
        .brand-badge {
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: .04em;
        }
        .nav {
            display: flex;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
        }
        .nav a {
            padding: 7px 12px;
            border-radius: 7px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 13.5px;
            white-space: nowrap;
            transition: background .15s, color .15s;
        }
        .nav a:hover {
            background: var(--surface-2);
            color: var(--text);
            text-decoration: none;
        }
        .nav a.active {
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 600;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Layout */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        .page-title {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -.01em;
        }
        .page-subtitle {
            margin: 2px 0 0;
            color: var(--text-muted);
            font-size: 13px;
        }

        /* Grid & Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        @media (max-width: 1024px) {
            .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .topbar-inner { padding: 0 12px; }
            .container { padding: 16px 12px; }
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 18px 20px;
        }
        .card-flush { padding: 0; overflow: hidden; }
        .card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .card-title {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
        }
        .kpi-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .kpi-value {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 6px;
        }
        .kpi-meta {
            font-size: 12.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 9px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.5;
        }
        .badge-success { background: var(--success-bg); color: var(--success); }
        .badge-warning { background: var(--warning-bg); color: var(--warning); }
        .badge-danger { background: var(--danger-bg); color: var(--danger); }
        .badge-info { background: var(--info-bg); color: var(--info); }
        .badge-neutral { background: #f1f5f9; color: #475569; }
        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
            display: inline-block;
        }

        /* Buttons & Forms */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s, border-color .15s;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn:hover { background: var(--surface-2); text-decoration: none; }
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }
        .btn-sm { padding: 4px 10px; font-size: 12px; }
        .input, .select {
            padding: 7px 11px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            font-size: 13px;
        }
        .input:focus, .select:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Key-value list */
        .kv-list { display: flex; flex-direction: column; gap: 8px; }
        .kv-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13.5px;
        }
        .kv-row:last-child { border-bottom: 0; }
        .kv-key { color: var(--text-muted); }
        .kv-val { font-weight: 600; }

        /* Tables */
        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }
        thead th {
            background: var(--surface-2);
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:last-child td { border-bottom: 0; }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 6px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 16px;
            font-weight: 500;
            color: var(--text-muted);
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            font-size: 14px;
        }
        .tab:hover { color: var(--text); text-decoration: none; }
        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            font-weight: 600;
        }

        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            border-top: 1px solid var(--border);
            background: var(--surface-2);
            font-size: 13px;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 10px;
        }
        .pagination-links { display: flex; gap: 6px; align-items: center; }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13.5px;
            border: 1px solid transparent;
        }
        .alert-danger { background: var(--danger-bg); color: var(--danger); border-color: #fecaca; }
        .alert-warning { background: var(--warning-bg); color: var(--warning); border-color: #fde68a; }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div style="display:flex;align-items:center;gap:20px;min-width:0;">
                <a href="{{ route('dashboard') }}" class="brand" style="text-decoration:none;">
                    <span class="brand-badge">CDV</span>
                    <span>TCPOS ↔ WooCommerce</span>
                </a>
                <nav class="nav">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('tables') }}" class="{{ request()->routeIs('tables') ? 'active' : '' }}">Tables &amp; Recherche</a>
                    <a href="/jobs">Jobs</a>
                    <a href="/health">Santé</a>
                    <a href="/logs">Logs</a>
                </nav>
            </div>
            <div class="topbar-actions">
                @yield('topbar_actions')
            </div>
        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
