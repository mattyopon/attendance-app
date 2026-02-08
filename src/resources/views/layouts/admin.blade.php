<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '勤怠管理アプリ - 管理者')</title>
    <style>
        /* Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, 'Hiragino Sans', 'Hiragino Kaku Gothic ProN', Meiryo, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            min-width: 1400px;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Header */
        .header {
            background-color: #0f172a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            height: 60px;
        }

        .header__logo {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            text-decoration: none;
        }

        .header__logo:hover {
            text-decoration: none;
            opacity: 0.9;
        }

        .header__nav {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .header__nav a {
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 4px;
            transition: color 0.15s;
        }

        .header__nav a:hover {
            color: #fff;
            text-decoration: none;
        }

        .header__nav .nav-logout-btn {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.4);
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 500;
            padding: 6px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }

        .header__nav .nav-logout-btn:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        /* Main */
        .main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        /* Alert */
        .alert {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert--success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert--error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Page Header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-header__title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Date Navigation */
        .date-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            margin-bottom: 24px;
        }

        .date-nav__arrow {
            color: #2563eb;
            font-size: 14px;
            font-weight: 500;
            padding: 6px 12px;
            border: 1px solid #2563eb;
            border-radius: 4px;
            transition: background 0.15s;
        }

        .date-nav__arrow:hover {
            background-color: #2563eb;
            color: #fff;
            text-decoration: none;
        }

        .date-nav__current {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            min-width: 200px;
            text-align: center;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
        }

        .tabs__item {
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: color 0.15s, border-color 0.15s;
        }

        .tabs__item:hover {
            color: #2563eb;
            text-decoration: none;
        }

        .tabs__item--active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        .table td {
            padding: 12px 16px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .table tbody tr:hover {
            background-color: #eff6ff;
        }

        .table__link {
            color: #2563eb;
            font-weight: 500;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge--pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge--approved {
            background-color: #dcfce7;
            color: #166534;
        }

        /* Pagination */
        .pagination {
            margin-top: 24px;
            display: flex;
            justify-content: center;
        }

        .pagination nav {
            display: flex;
            gap: 4px;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 8px;
            font-size: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            color: #2563eb;
            transition: background 0.15s;
        }

        .pagination a:hover {
            background-color: #eff6ff;
            text-decoration: none;
        }

        .pagination span[aria-current="page"] span,
        .pagination .active span {
            background-color: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .pagination .disabled span {
            color: #94a3b8;
            background-color: #f8fafc;
        }

        /* Auth */
        .auth {
            max-width: 480px;
            margin: 60px auto 0;
            background: #fff;
            padding: 40px 48px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .auth__title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            margin-bottom: 32px;
        }

        .auth__link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        /* Form */
        .form__group {
            margin-bottom: 20px;
        }

        .form__label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form__input {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #333;
            transition: border-color 0.15s;
        }

        .form__input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .form__button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background-color: #0f172a;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.15s;
            margin-top: 8px;
        }

        .form__button:hover {
            background-color: #1e293b;
        }

        .form__error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 4px;
        }

        /* Detail */
        .detail {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 32px 40px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .detail__title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 28px;
            text-align: center;
        }

        .detail__row {
            display: flex;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail__label {
            width: 140px;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            flex-shrink: 0;
        }

        .detail__value {
            flex: 1;
            font-size: 14px;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail__input {
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            width: 120px;
            text-align: center;
        }

        .detail__input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .detail__input:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
        }

        .detail__textarea {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        .detail__textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .detail__textarea:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
        }

        .detail__actions {
            margin-top: 28px;
            text-align: center;
        }

        .detail__button {
            padding: 10px 40px;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            background-color: #2563eb;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .detail__button:hover {
            background-color: #1d4ed8;
        }

        .detail__button:disabled {
            background-color: #94a3b8;
            cursor: not-allowed;
        }

        /* Export Button */
        .export-button {
            display: inline-block;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background-color: #059669;
            border-radius: 6px;
            transition: background-color 0.15s;
        }

        .export-button:hover {
            background-color: #047857;
            text-decoration: none;
        }

        /* Approve Button */
        .approve-button {
            padding: 6px 20px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            background-color: #2563eb;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .approve-button:hover {
            background-color: #1d4ed8;
        }

        .approve-button:disabled {
            background-color: #94a3b8;
            cursor: not-allowed;
        }

        /* Correction Detail */
        .correction-detail {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 32px 40px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .correction-detail__title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 28px;
            text-align: center;
        }

        .correction-detail__section {
            margin-bottom: 24px;
        }

        .correction-detail__section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #dbeafe;
        }

        .correction-detail__row {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .correction-detail__label {
            width: 140px;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            flex-shrink: 0;
        }

        .correction-detail__value {
            flex: 1;
            font-size: 14px;
            color: #334155;
        }

        .correction-detail__actions {
            margin-top: 28px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="/admin/attendance/list" class="header__logo">勤怠管理アプリ</a>
        @auth
        <nav class="header__nav">
            <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
            <a href="{{ route('admin.stamp_correction.list') }}">申請一覧</a>
            <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="nav-logout-btn">ログアウト</button>
            </form>
        </nav>
        @endauth
    </header>

    <main class="main">
        @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
