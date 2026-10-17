<style>
    .gc-wrap { max-width: 820px; margin: 0 auto; }
    .gc-wrap .page-title { font-size: 1.35rem; font-weight: 600; color: #1f2430; letter-spacing: -0.02em; }
    .gc-prompt,
    .gc-card {
        display: block;
        background: #fff;
        border: 1px solid #e6e8ee;
        border-radius: 16px;
        margin-bottom: 16px;
        color: inherit;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(31, 36, 48, 0.04);
    }
    .gc-prompt { display: flex; align-items: center; gap: 16px; padding: 14px 18px; }
    .gc-prompt:hover { border-color: #c9d2ea; box-shadow: 0 8px 24px rgba(61, 94, 225, 0.08); color: inherit; }
    .gc-prompt span { color: #5c6578; font-size: 0.95rem; }
    .gc-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex: 0 0 40px; background: #eef1f6; }
    .gc-card-body { padding: 20px 22px 8px; }
    .gc-head { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
    .gc-head-text { min-width: 0; flex: 1; }
    .gc-name { font-weight: 600; color: #1f2430; line-height: 1.2; }
    .gc-meta { color: #6b7287; font-size: 0.8rem; margin-top: 2px; }
    .gc-title { font-size: 1.05rem; font-weight: 600; color: #1f2430; margin: 14px 0 6px; }
    .gc-title a { color: inherit; text-decoration: none; }
    .gc-title a:hover { color: #3d5ee1; }
    .gc-body { color: #1f2430; white-space: pre-wrap; line-height: 1.55; margin: 0; }
    .gc-files, .gc-file-list { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 4px; padding: 0; list-style: none; }
    .gc-file, .gc-file-list li {
        display: inline-flex; align-items: center; gap: 6px;
        border: 1px solid #e6e8ee; border-radius: 10px; padding: 6px 10px;
        color: #3d5ee1; font-size: 0.82rem; text-decoration: none; background: #f7f8fc;
    }
    .gc-more, .gc-icon-btn {
        border: 0; background: transparent; color: #5c6578; width: 40px; height: 40px;
        border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .gc-more:hover, .gc-icon-btn:hover { background: #f3f5f9; color: #1f2430; }
    .gc-field { margin-bottom: 14px; }
    .gc-field > label, .gc-option:not(.gc-option-check) > span { display: block; font-size: 0.78rem; font-weight: 600; color: #5c6578; margin-bottom: 6px; }
    .gc-field input, .gc-field textarea, .gc-option select, .gc-option input[type="datetime-local"] {
        width: 100%; border: 1px solid #e3e6ee; border-radius: 12px; background: #f7f8fc;
        color: #1f2430; font-size: 0.95rem; padding: 12px 14px; outline: none;
    }
    .gc-field textarea { min-height: 120px; resize: vertical; line-height: 1.5; }
    .gc-field input:focus, .gc-field textarea:focus, .gc-option select:focus, .gc-option input:focus {
        background: #fff; border-color: #3d5ee1; box-shadow: 0 0 0 3px rgba(61, 94, 225, 0.12);
    }
    .gc-classes { margin-top: 6px; padding-top: 6px; }
    .gc-classes-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 8px; }
    .gc-classes-head h2 { font-size: 0.95rem; font-weight: 600; color: #1f2430; margin: 0; }
    .gc-classes-head p, .gc-picked, .gc-note { color: #6b7287; font-size: 0.82rem; margin: 2px 0 0; }
    .gc-picked { margin-top: 10px; }
    .gc-grade {
        display: grid; grid-template-columns: 132px minmax(0, 1fr) auto;
        gap: 10px; align-items: center; padding: 10px 0; border-top: 1px solid #eef0f4;
    }
    .gc-grade-name { font-size: 0.86rem; font-weight: 600; color: #1f2430; }
    .gc-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .gc-chip {
        position: relative; display: inline-flex; align-items: center; min-height: 36px;
        border: 1px solid #e3e6ee; border-radius: 999px; padding: 6px 14px;
        background: #fff; color: #3c4456; font-size: 0.86rem; cursor: pointer; user-select: none;
    }
    .gc-chip input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .gc-chip:focus-within { outline: 2px solid #3d5ee1; outline-offset: 2px; }
    .gc-chip:has(input:checked) { background: #eef2ff; border-color: #3d5ee1; color: #24356e; font-weight: 600; }
    .gc-chip:has(input:checked) span::before { content: "✓  "; }
    .gc-text-btn {
        border: 0; background: transparent; color: #3d5ee1; font-weight: 600; font-size: 0.82rem;
        padding: 6px 8px; border-radius: 8px; white-space: nowrap;
    }
    .gc-text-btn:hover { background: #eef2ff; }
    .gc-extra { margin-top: 14px; padding: 14px; border-radius: 14px; background: #f7f8fc; }
    .gc-extra-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .gc-option-check {
        display: flex; align-items: center; gap: 8px; grid-column: 1 / -1;
        min-height: 44px; margin: 0;
    }
    .gc-option-check span { margin: 0; font-size: 0.9rem; font-weight: 500; color: #1f2430; }
    .gc-bar {
        display: flex; align-items: center; gap: 8px;
        border-top: 1px solid #eef0f4; padding: 10px 14px 12px;
    }
    .gc-count { color: #6b7287; font-size: 0.8rem; }
    .gc-post-btn {
        margin-left: auto; background: #3d5ee1; color: #fff; border: 0; border-radius: 10px;
        padding: 10px 22px; font-weight: 600;
    }
    .gc-post-btn:hover { background: #314db8; color: #fff; }
    .gc-cancel { color: #5c6578; text-decoration: none; font-weight: 600; padding: 8px 10px; border-radius: 8px; }
    .gc-cancel:hover { background: #f3f5f9; color: #1f2430; }
    .gc-empty { text-align: center; color: #6b7287; padding: 48px 16px; }
    .gc-error { color: #d93025; font-size: 0.82rem; margin-top: 6px; }
    @media (max-width: 720px) {
        .gc-grade { grid-template-columns: 1fr; }
        .gc-extra-grid { grid-template-columns: 1fr; }
        .gc-bar { flex-wrap: wrap; }
    }
</style>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/announcements/partials/classroom-styles.blade.php ENDPATH**/ ?>