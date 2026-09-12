<style>
    @page {
        size: A4 landscape;
        margin: 8mm;
    }
    * { box-sizing: border-box; }
    body {
        font-family: "Times New Roman", Times, serif;
        color: #000;
        margin: 0;
        padding: 12px;
        font-size: 11px;
        background: #fff;
    }
    .actions {
        margin-bottom: 12px;
        text-align: right;
    }
    .actions button {
        padding: 8px 16px;
        cursor: pointer;
        font-size: 14px;
    }
    .report-sheet {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        border: 1.5px solid #000;
        padding: 10px 12px 14px;
    }
    .school-header {
        text-align: center;
        margin-bottom: 8px;
        border-bottom: 1px solid #000;
        padding-bottom: 6px;
    }
    .school-header .school-name {
        font-size: 15px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin: 0;
    }
    .school-header .doc-title {
        font-size: 12px;
        font-weight: bold;
        margin: 2px 0 0;
        text-transform: uppercase;
    }
    .learner-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 18px;
        margin: 8px 0 10px;
        font-size: 12px;
    }
    .learner-meta strong { font-weight: bold; }
    .two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        align-items: start;
    }
    .col h3 {
        margin: 0 0 6px;
        text-align: center;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }
    table.rc {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 0 0 6px;
    }
    table.rc th,
    table.rc td {
        border: 1px solid #000;
        padding: 3px 4px;
        vertical-align: middle;
        font-size: 10px;
        line-height: 1.25;
    }
    table.rc th {
        text-align: center;
        font-weight: bold;
        background: #fff;
    }
    table.rc .center { text-align: center; }
    table.rc .left { text-align: left; }
    table.rc .core { font-weight: bold; text-align: left; width: 18%; }
    table.rc .indent { padding-left: 14px; }
    table.rc .ga-label {
        font-weight: bold;
        text-align: left;
        text-transform: uppercase;
    }
    table.rc .scale th,
    table.rc .scale td { font-size: 10px; padding: 3px 5px; }
    .marking {
        font-size: 10px;
        margin: 2px 0 12px;
        line-height: 1.35;
    }
    .marking strong { display: inline-block; min-width: 28px; }
    .att-title {
        text-align: center;
        font-weight: bold;
        font-size: 12px;
        text-transform: uppercase;
        margin: 10px 0 6px;
    }
    .page-break {
        page-break-after: always;
        break-after: page;
        margin-bottom: 24px;
    }
    .page-break:last-child {
        page-break-after: auto;
        break-after: auto;
        margin-bottom: 0;
    }
    @media print {
        .actions { display: none !important; }
        body { padding: 0; }
        .report-sheet {
            max-width: none;
            border: 1.5px solid #000;
        }
    }
    @media screen and (max-width: 900px) {
        .two-col { grid-template-columns: 1fr; }
    }
</style>
