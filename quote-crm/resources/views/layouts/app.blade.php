<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} – @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --sidebar-width: 220px;
            --topbar-height: 60px;
            --bg-app:       #f0f2f5;
            --bg-sidebar:   #1a237e;
            --bg-sidebar-hover: rgba(255,255,255,0.1);
            --bg-sidebar-active: rgba(255,255,255,0.18);
            --bg-white:     #ffffff;
            --bg-card:      #ffffff;
            --border:       #e2e8f0;
            --border-light: #f1f5f9;
            --accent:       #2563eb;
            --accent-hover: #1d4ed8;
            --accent-soft:  #eff6ff;
            --success:      #16a34a;
            --success-soft: #f0fdf4;
            --success-border: #bbf7d0;
            --warning:      #d97706;
            --warning-soft: #fffbeb;
            --warning-border: #fde68a;
            --danger:       #dc2626;
            --danger-soft:  #fef2f2;
            --danger-border: #fecaca;
            --purple:       #7c3aed;
            --purple-soft:  #f5f3ff;
            --purple-border: #ddd6fe;
            --teal:         #0891b2;
            --teal-soft:    #ecfeff;
            --text-primary:   #0f172a;
            --text-secondary: #475569;
            --text-muted:     #94a3b8;
            --radius:    12px;
            --radius-sm:  8px;
            --radius-xs:  6px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow:    0 4px 16px rgba(0,0,0,0.08);
            --shadow-md: 0 8px 32px rgba(0,0,0,0.12);
        }
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-app);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.5;
        }

        /* ── Sidebar ──────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-logo-icon {
            width: 34px; height: 34px;
            background: #fff;
            border-radius: var(--radius-xs);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-logo-icon svg { width: 18px; height: 18px; color: var(--bg-sidebar); }
        .sidebar-logo-text { font-weight: 700; font-size: 15px; color: #fff; letter-spacing: -0.3px; }

        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 20px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 13.5px; font-weight: 500;
            transition: all 0.15s;
            position: relative;
            border-radius: 0;
        }
        .nav-item:hover { color: #fff; background: var(--bg-sidebar-hover); }
        .nav-item.active { color: #fff; background: var(--bg-sidebar-active); font-weight: 600; }
        .nav-item.active::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 3px; background: #fff;
            border-radius: 0 2px 2px 0;
        }
        .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; opacity: 0.85; }
        .nav-item.active svg { opacity: 1; }
        .nav-badge {
            margin-left: auto;
            background: rgba(255,255,255,0.2);
            color: #fff; font-size: 10px; font-weight: 700;
            padding: 1px 7px; border-radius: 20px;
        }
        .nav-section-label {
            font-size: 10px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: rgba(255,255,255,0.4);
            padding: 12px 20px 4px;
        }

        .sidebar-footer { border-top: 1px solid rgba(255,255,255,0.1); padding: 14px; }
        .user-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: background 0.15s;
        }
        .user-card:hover { background: rgba(255,255,255,0.15); }
        .user-avatar {
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .user-name { font-size: 13px; font-weight: 600; color: #fff; }
        .user-role { font-size: 11px; color: rgba(255,255,255,0.55); }

        /* ── Topbar ───────────────────────────────── */
        .main-wrapper { margin-left: var(--sidebar-width); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar {
            height: var(--topbar-height);
            background: var(--bg-white);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center;
            padding: 0 24px; gap: 16px;
            position: sticky; top: 0; z-index: 50;
            box-shadow: var(--shadow-sm);
        }
        .topbar-title { font-size: 17px; font-weight: 700; color: var(--text-primary); flex: 1; }

        /* Global search */
        .topbar-search {
            display: flex; align-items: center; gap: 8px;
            background: var(--bg-app);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 14px;
            width: 280px;
        }
        .topbar-search svg { width: 15px; height: 15px; color: var(--text-muted); flex-shrink: 0; }
        .topbar-search input {
            border: none; background: transparent; outline: none;
            font-size: 13px; color: var(--text-primary); width: 100%;
            font-family: 'Inter', sans-serif;
        }
        .topbar-search input::placeholder { color: var(--text-muted); }

        /* Topbar icons */
        .topbar-icon {
            width: 36px; height: 36px;
            border-radius: var(--radius-xs);
            display: flex; align-items: center; justify-content: center;
            background: transparent; border: 1px solid var(--border);
            cursor: pointer; transition: all 0.15s; position: relative;
        }
        .topbar-icon:hover { background: var(--bg-app); }
        .topbar-icon svg { width: 17px; height: 17px; color: var(--text-secondary); }
        .topbar-dot {
            position: absolute; top: 6px; right: 6px;
            width: 7px; height: 7px;
            background: var(--danger); border-radius: 50%;
            border: 1.5px solid #fff;
        }
        .topbar-avatar {
            width: 34px; height: 34px;
            background: var(--accent);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #fff;
            cursor: pointer;
        }

        /* ── Buttons ──────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px; border-radius: var(--radius-xs);
            font-size: 13px; font-weight: 600; cursor: pointer;
            text-decoration: none; border: none; transition: all 0.15s;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); box-shadow: 0 4px 12px rgba(37,99,235,0.3); }
        .btn-ghost { background: #fff; color: var(--text-secondary); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--bg-app); color: var(--text-primary); }
        .btn-success { background: var(--success); color: #fff; }
        .btn-danger  { background: var(--danger);  color: #fff; }
        .btn svg { width: 14px; height: 14px; }
        .btn-sm { padding: 5px 12px; font-size: 12px; }

        /* ── Page ─────────────────────────────────── */
        .page-content { padding: 24px; flex: 1; }

        /* ── Status badges ────────────────────────── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }
        .badge-new              { background: var(--accent-soft);   color: var(--accent);   border: 1px solid #bfdbfe; }
        .badge-in_review        { background: var(--purple-soft);   color: var(--purple);   border: 1px solid var(--purple-border); }
        .badge-rfq_sent         { background: var(--warning-soft);  color: var(--warning);  border: 1px solid var(--warning-border); }
        .badge-pricing_received { background: var(--teal-soft);     color: var(--teal);     border: 1px solid #a5f3fc; }
        .badge-quote_prepared   { background: var(--purple-soft);   color: var(--purple);   border: 1px solid var(--purple-border); }
        .badge-quote_sent       { background: var(--accent-soft);   color: var(--accent);   border: 1px solid #bfdbfe; }
        .badge-submitted        { background: var(--accent-soft);   color: var(--accent);   border: 1px solid #bfdbfe; }
        .badge-won              { background: var(--success-soft);  color: var(--success);  border: 1px solid var(--success-border); }
        .badge-lost             { background: var(--danger-soft);   color: var(--danger);   border: 1px solid var(--danger-border); }
        .badge-cancelled        { background: #f8fafc;              color: var(--text-muted); border: 1px solid var(--border); }
        .badge-pending          { background: var(--warning-soft);  color: var(--warning);  border: 1px solid var(--warning-border); }
        .badge-open             { background: #f8fafc;              color: var(--text-secondary); border: 1px solid var(--border); }

        /* ── Card ─────────────────────────────────── */
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); }
        .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }

        /* ── Table ────────────────────────────────── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 11.5px; font-weight: 600; color: var(--text-muted); padding: 10px 16px; text-align: left; border-bottom: 1px solid var(--border); background: #fafbfc; white-space: nowrap; }
        td { padding: 11px 16px; border-bottom: 1px solid var(--border-light); font-size: 13px; color: var(--text-secondary); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbfd; }
        .td-bold { color: var(--text-primary); font-weight: 600; }

        /* ── Flash ────────────────────────────────── */
        .flash { padding: 12px 16px; border-radius: var(--radius-xs); margin-bottom: 20px; font-size: 13px; font-weight: 500; }
        .flash-success { background: var(--success-soft); color: var(--success); border: 1px solid var(--success-border); }
        .flash-error   { background: var(--danger-soft);  color: var(--danger);  border: 1px solid var(--danger-border); }

        /* ── Form controls ────────────────────────── */
        .form-label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 9px 12px;
            border: 1px solid var(--border); border-radius: var(--radius-xs);
            font-size: 13px; font-family: 'Inter', sans-serif;
            color: var(--text-primary); background: #fff;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        select.form-control { cursor: pointer; }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <span class="sidebar-logo-text">QuoteCRM</span>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('quotes.index') }}" class="nav-item {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Quotes
        </a>
        <a href="{{ route('contacts.index') }}" class="nav-item {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Contacts
        </a>
        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Companies
        </a>
        <a href="{{ route('vendors.index') }}" class="nav-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Vendors
        </a>
        <a href="{{ route('emails.index') }}" class="nav-item {{ request()->routeIs('emails.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Emails
            <span class="nav-badge" id="email-badge" style="display:none">0</span>
        </a>
        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Reports
        </a>
        @role('admin')
        <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
            Settings
        </a>
        @endrole
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}" id="logout-form">@csrf</form>
        <div class="user-card" onclick="document.getElementById('logout-form').submit()">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->getRoleNames()->first() ?? 'User') }} · Sign out</div>
            </div>
        </div>
    </div>
</aside>

{{-- Main --}}
<div class="main-wrapper">
    <header class="topbar">
        {{-- Hamburger placeholder --}}
        <button style="background:none;border:none;cursor:pointer;padding:4px;color:var(--text-secondary)" onclick="">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>

        {{-- Global search --}}
        <div class="topbar-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search quotes, companies, contacts..." id="global-search">
        </div>

        {{-- Actions from child pages --}}
        @yield('topbar-actions')

        {{-- Notifications --}}
        <div class="topbar-icon" title="Notifications">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="topbar-dot"></span>
        </div>

        {{-- Help --}}
        <div class="topbar-icon" title="Help">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>

        {{-- User avatar --}}
        <div class="topbar-avatar" title="{{ auth()->user()->name }}">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))<div class="flash flash-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="flash flash-error">{{ session('error') }}</div>@endif
        @yield('content')
    </main>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>
