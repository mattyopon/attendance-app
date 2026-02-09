<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '勤怠管理アプリ')</title>
    <style>
        /* Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, 'Hiragino Sans', 'Hiragino Kaku Gothic ProN', Meiryo, sans-serif;
            background-color: #eee;
            color: #333;
            line-height: 1.6;
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
            background-color: #111;
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
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 4px;
            transition: color 0.15s;
        }

        .header__nav a:hover {
            color: #ccc;
            text-decoration: none;
        }

        .header__nav .nav-logout-btn {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            padding: 6px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: color 0.15s;
        }

        .header__nav .nav-logout-btn:hover {
            color: #ccc;
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
            border-left: 3px solid #333;
            padding-left: 12px;
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
            color: #333;
            font-size: 14px;
            font-weight: normal;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            transition: color 0.15s;
        }

        .date-nav__arrow:hover {
            color: #666;
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
            color: #333;
            text-decoration: none;
        }

        .tabs__item--active {
            color: #333;
            font-weight: bold;
            border-bottom-color: #333;
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
            background-color: #f8f8f8;
            color: #555;
            font-size: 13px;
            font-weight: 600;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table td {
            padding: 12px 16px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:hover {
            background-color: #fafafa;
        }

        .table__link {
            color: #333;
            font-weight: bold;
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
            padding: 40px;
            border-radius: 8px;
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
            border-radius: 4px;
            background: #fff;
            color: #333;
            transition: border-color 0.15s;
        }

        .form__input:focus {
            outline: none;
            border-color: #333;
        }

        .form__button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.15s;
            margin-top: 8px;
        }

        .form__button:hover {
            background-color: #555;
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
            border-radius: 0px;
        }

        .detail__title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 28px;
            text-align: left;
            border-left: 3px solid #333;
            padding-left: 12px;
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
            border-color: #333;
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
            border-color: #333;
        }

        .detail__textarea:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
        }

        .detail__actions {
            margin-top: 28px;
            text-align: right;
        }

        .detail__button {
            padding: 10px 40px;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .detail__button:hover {
            background-color: #555;
        }

        .detail__button:disabled {
            background-color: #999;
            cursor: not-allowed;
        }

        /* Attendance (Clock) */
        .attendance {
            text-align: center;
            padding: 80px 0;
        }

        .attendance__status {
            font-size: 18px;
            font-weight: 600;
            color: #555;
            margin-bottom: 16px;
            padding: 6px 20px;
            display: inline-block;
            border-radius: 20px;
            background-color: #ddd;
        }

        .attendance__status--working {
            color: #555;
            background-color: #ddd;
        }

        .attendance__status--on_break {
            color: #555;
            background-color: #ddd;
        }

        .attendance__status--left {
            color: #555;
            background-color: #ddd;
        }

        .attendance__date {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .attendance__time {
            font-size: 64px;
            font-weight: bold;
            color: #333;
            letter-spacing: 0;
            margin-bottom: 40px;
        }

        .attendance__buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .attendance__button {
            padding: 14px 48px;
            font-size: 18px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.15s, transform 0.1s;
        }

        .attendance__button:hover {
            transform: translateY(-1px);
        }

        .attendance__button:active {
            transform: translateY(0);
        }

        .attendance__button--clock-in {
            background-color: #333;
            color: #fff;
        }

        .attendance__button--clock-in:hover {
            background-color: #555;
        }

        .attendance__button--clock-out {
            background-color: #333;
            color: #fff;
        }

        .attendance__button--clock-out:hover {
            background-color: #555;
        }

        .attendance__button--rest-start {
            background-color: #fff;
            color: #333;
            border: 1px solid #333;
        }

        .attendance__button--rest-start:hover {
            background-color: #f5f5f5;
        }

        .attendance__button--rest-end {
            background-color: #fff;
            color: #333;
            border: 1px solid #333;
        }

        .attendance__button--rest-end:hover {
            background-color: #f5f5f5;
        }

        .attendance__message {
            font-size: 24px;
            font-weight: normal;
            color: #333;
        }

        /* Export Button */
        .export-button {
            display: inline-block;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background-color: #333;
            border-radius: 6px;
            transition: background-color 0.15s;
        }

        .export-button:hover {
            background-color: #555;
            text-decoration: none;
        }

        /* Approve Button */
        .approve-button {
            padding: 6px 20px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .approve-button:hover {
            background-color: #555;
        }

        .approve-button:disabled {
            background-color: #999;
            cursor: not-allowed;
        }

        /* Correction Detail */
        .correction-detail {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 32px 40px;
            border-radius: 0px;
        }

        .correction-detail__title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 28px;
            text-align: left;
            border-left: 3px solid #333;
            padding-left: 12px;
        }

        .correction-detail__section {
            margin-bottom: 24px;
        }

        .correction-detail__section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
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
        <a href="/" class="header__logo"><img src="{{ asset('img/logo.png') }}" alt="COACHTECH" height="20"></a>
        @auth
        <nav class="header__nav">
            <a href="{{ route('attendance.index') }}">勤怠</a>
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('stamp_correction.list') }}">申請</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
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
