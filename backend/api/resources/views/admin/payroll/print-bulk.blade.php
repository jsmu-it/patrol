<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tanda Terima Gaji - Bulk Print</title>
  <style>
    :root{
      --ink:#111;
      --line:#222;
      --muted:#666;
      --green:#0a7c3e;
      --red:#c00000;
      --blue:#0b57d0;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      background:#f5f6f7;
      font-family: Arial, Helvetica, sans-serif;
      color:var(--ink);
    }
    .page{
      width: 1100px;
      margin: 24px auto;
      background:#fff;
      padding: 26px 30px;
      border: 1px solid #ddd;
      page-break-after: always;
    }
    .page:last-child{
      page-break-after: auto;
    }

    .top-row{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:16px;
    }
    .company{
      font-weight:700;
      font-size:14px;
    }
    .title{
      text-align:center;
      font-weight:700;
      font-size:18px;
      letter-spacing:.4px;
      flex:1;
      margin-top:2px;
    }
    .rule{
      border-top:2px solid var(--line);
      margin: 10px 0 14px;
    }

    .meta{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      font-size:13px;
    }
    .meta table{
      width:100%;
      border-collapse:collapse;
    }
    .meta td{
      padding:2px 0;
      vertical-align:top;
    }
    .meta .k{
      width:92px;
      font-weight:700;
    }
    .meta .sep{
      width:14px;
      text-align:center;
      font-weight:700;
    }

    .main-table{
      width:100%;
      border-collapse:collapse;
      margin-top:10px;
      font-size:13px;
    }
    .main-table th,
    .main-table td{
      border:1px solid var(--line);
      padding:8px 10px;
      vertical-align:top;
    }
    .main-table th{
      text-align:center;
      font-weight:700;
      background:#fff;
    }
    .main-table th.income-header{
      background:#e2efda;
      color:var(--green);
    }
    .main-table th.deduction-header{
      background:#fce4d6;
      color:var(--red);
    }
    .col-2{
      width:50%;
    }

    .items{
      width:100%;
      border-collapse:collapse;
    }
    .items td{
      border:none;
      padding:4px 0;
    }
    .items .label{
      padding-right:12px;
      white-space:nowrap;
    }
    .items .sep{
      width:14px;
      text-align:center;
      font-weight:700;
    }
    .items .amt{
      text-align:right;
      width:140px;
      font-variant-numeric: tabular-nums;
      white-space:nowrap;
    }

    .summary-section{
      margin-top: 16px;
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }

    .summary-box{
      border: 2px solid var(--line);
      padding: 12px 16px;
    }
    .summary-box.total-income{
      border-color: var(--green);
      background: #f0fdf4;
    }
    .summary-box.total-deduction{
      border-color: var(--red);
      background: #fef2f2;
    }
    .summary-box .summary-label{
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }
    .summary-box.total-income .summary-label{
      color: var(--green);
    }
    .summary-box.total-deduction .summary-label{
      color: var(--red);
    }
    .summary-box .summary-value{
      font-size: 20px;
      font-weight: 700;
    }
    .summary-box.total-income .summary-value{
      color: var(--green);
    }
    .summary-box.total-deduction .summary-value{
      color: var(--red);
    }

    .net-income-section{
      margin-top: 16px;
      background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
      padding: 16px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .net-income-section .net-label{
      color: #fff;
      font-size: 14px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .net-income-section .net-value{
      color: #fff;
      font-size: 28px;
      font-weight: 700;
    }

    .bottom{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-top: 20px;
      align-items:end;
      font-size:13px;
    }

    .sign{
      text-align:center;
    }
    .sign .place-date{
      text-align:right;
      margin-bottom:4px;
    }
    .sign .who{
      text-align:right;
      margin-bottom:42px;
    }
    .sign .name{
      font-weight:700;
      margin-top:6px;
    }
    .sign .line{
      border-top:1px solid var(--line);
      width:240px;
      margin: 0 0 6px auto;
    }

    .note{
      font-size: 11px;
      color: var(--muted);
      font-style: italic;
    }

    @media print{
      body{ background:#fff; }
      .page{
        width:auto;
        margin:0;
        border:none;
        padding:0;
      }
      @page{
        size: A4 landscape;
        margin: 12mm;
      }
    }
    @media (max-width: 1200px){
      .page{ width: calc(100% - 24px); }
    }
  </style>
</head>
<body>
  @foreach($slips as $slip)
  <div class="page">
    <div class="top-row">
      <div class="company">PT JAYA SAKTI MANDIRI UNGGUL</div>
      <div class="title">TANDA TERIMA GAJI BULAN&nbsp;&nbsp;{{ strtoupper($slip->period_month) }}</div>
      <div style="width:160px"></div>
    </div>

    <div class="rule"></div>

    <div class="meta">
      <table>
        <tr>
          <td class="k">NAMA</td><td class="sep">:</td><td><b>{{ $slip->name }}</b></td>
        </tr>
        <tr>
          <td class="k">NIP</td><td class="sep">:</td><td><b>{{ $slip->nip }}</b></td>
        </tr>
      </table>

      <table>
        <tr>
          <td class="k">UNIT / SITE</td><td class="sep">:</td><td><b>{{ $slip->unit ?? '-' }}</b></td>
        </tr>
        <tr>
          <td class="k">JABATAN</td><td class="sep">:</td><td><b>{{ $slip->position ?? '-' }}</b></td>
        </tr>
      </table>
    </div>

    <table class="main-table">
      <tr>
        <th class="col-2 income-header">PENERIMAAN</th>
        <th class="col-2 deduction-header">POTONGAN</th>
      </tr>
      <tr>
        <td>
          <table class="items">
            @foreach($slip->incomeItems as $item)
            <tr><td class="label">{{ $item->label }}</td><td class="sep">:</td><td class="amt">{{ number_format($item->amount, 0, ',', '.') }}</td></tr>
            @endforeach
          </table>
        </td>
        <td>
          <table class="items">
            @foreach($slip->deductionItems as $item)
            <tr><td class="label">{{ $item->label }}</td><td class="sep">:</td><td class="amt">{{ number_format($item->amount, 0, ',', '.') }}</td></tr>
            @endforeach
          </table>
        </td>
      </tr>
    </table>

    <div class="summary-section">
      <div class="summary-box total-income">
        <div class="summary-label">Total Pendapatan</div>
        <div class="summary-value">Rp {{ number_format($slip->total_income, 0, ',', '.') }}</div>
      </div>
      <div class="summary-box total-deduction">
        <div class="summary-label">Total Potongan</div>
        <div class="summary-value">Rp {{ number_format($slip->total_deduction, 0, ',', '.') }}</div>
      </div>
    </div>

    <div class="net-income-section">
      <div class="net-label">Pendapatan Diterima</div>
      <div class="net-value">Rp {{ number_format($slip->net_income, 0, ',', '.') }}</div>
    </div>

    <div class="bottom">
      <div class="note">
        * Slip gaji ini adalah dokumen resmi dan bersifat rahasia.<br>
        * Jika ada pertanyaan, hubungi bagian HRD.
      </div>

      <div class="sign">
        <div class="place-date">{{ $slip->sign_location }}, {{ $slip->sign_date?->format('d/m/Y') }}</div>
        <div class="who">Yang menerima,</div>
        <div class="line"></div>
        <div class="name">({{ $slip->name }})</div>
      </div>
    </div>
  </div>
  @endforeach

  <script>
    window.onload = function() {
      window.print();
    }
  </script>
</body>
</html>
