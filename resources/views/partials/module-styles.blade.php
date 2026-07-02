{{-- Reusable page header with search + filter bar --}}
<style>
    .page-toolbar {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 20px; flex-wrap: wrap;
    }
    .search-box {
        display: flex; align-items: center; gap: 8px;
        background: #fff; border: 1px solid var(--border);
        border-radius: var(--radius-xs); padding: 8px 14px;
        flex: 1; min-width: 220px; max-width: 360px;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .search-box:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
    .search-box svg { width: 15px; height: 15px; color: var(--text-muted); flex-shrink: 0; }
    .search-box input { border: none; outline: none; background: transparent; font-size: 13px; font-family: 'Inter',sans-serif; color: var(--text-primary); width: 100%; }
    .search-box input::placeholder { color: var(--text-muted); }
    .filter-select { padding: 8px 12px; border: 1px solid var(--border); border-radius: var(--radius-xs); font-size: 13px; font-family: 'Inter',sans-serif; color: var(--text-secondary); background: #fff; outline: none; cursor: pointer; }
    .filter-select:focus { border-color: var(--accent); }

    /* Module list card */
    .list-card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
    .list-card-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid var(--border); }
    .list-card-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
    .list-count { font-size: 12px; color: var(--text-muted); margin-left: 6px; }

    /* Row actions */
    .row-actions { display: flex; gap: 6px; opacity: 0; transition: opacity 0.15s; }
    tr:hover .row-actions { opacity: 1; }
    .btn-icon { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--border); background: #fff; color: var(--text-secondary); cursor: pointer; text-decoration: none; transition: all 0.15s; }
    .btn-icon:hover { background: var(--bg-app); color: var(--text-primary); }
    .btn-icon.danger:hover { background: var(--danger-soft); border-color: var(--danger-border); color: var(--danger); }
    .btn-icon svg { width: 13px; height: 13px; }

    /* Avatar initials */
    .avatar-initials {
        width: 32px; height: 32px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
        background: var(--accent-soft); color: var(--accent);
    }

    /* Pagination */
    .pagination-wrap { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .pagination-info { font-size: 12.5px; color: var(--text-muted); }

    /* Detail page */
    .detail-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
    .detail-card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 16px; }
    .detail-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 16px; }
    .detail-avatar { width: 48px; height: 48px; border-radius: var(--radius-sm); background: var(--accent-soft); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; flex-shrink: 0; }
    .detail-name { font-size: 18px; font-weight: 700; color: var(--text-primary); }
    .detail-sub { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
    .detail-body { padding: 20px; }
    .detail-field { margin-bottom: 16px; }
    .detail-field-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 4px; }
    .detail-field-value { font-size: 13.5px; color: var(--text-primary); }

    /* Form page */
    .form-card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); max-width: 720px; }
    .form-card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); }
    .form-card-title { font-size: 16px; font-weight: 700; color: var(--text-primary); }
    .form-card-body { padding: 24px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
    .form-row.single { grid-template-columns: 1fr; }
    .form-group { display: flex; flex-direction: column; }
    .form-actions { display: flex; gap: 10px; padding: 16px 24px; border-top: 1px solid var(--border); }
    .form-error { font-size: 12px; color: var(--danger); margin-top: 4px; }
    .required-star { color: var(--danger); margin-left: 2px; }
    textarea.form-control { resize: vertical; min-height: 80px; }
</style>
