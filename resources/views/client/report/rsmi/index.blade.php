<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSMI - Report of Supplies and Materials Issued</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .rsmi-content {
            padding: 20px;
            background: white;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }

        .report-header h1 {
            color: #000;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #296218;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #1e4612;
            color: white;
        }

        .filters-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            align-items: end;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 6px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #296218;
            color: white;
        }

        .btn-primary:hover {
            background: #1e4612;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .report-table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }

        .report-table th {
            background-color: #e8f5e9;
            color: #000;
            font-weight: 600;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .export-btn {
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .export-btn.pdf {
            background-color: #e74c3c;
        }

        .export-btn.excel {
            background-color: #3498db;
        }

        .export-btn:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        .print-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #296218;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            cursor: pointer;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .print-button:hover {
            background: #1e4612;
            transform: scale(1.05);
        }

        .recap-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 30px;
        }

        .recap {
            width: 48%;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recap table {
            width: 100%;
            border-collapse: collapse;
        }

        .recap th,
        .recap td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        .recap th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            margin-bottom: 10px;
        }

        @media print {
            body {
                background: white;
            }

            .back-button, .filters-section, .print-button {
                display: none !important;
            }

            .rsmi-content {
                padding: 0;
            }

            .report-table th,
            .report-table td {
                border: 1px solid #000 !important;
                padding: 5px;
                font-size: 12px;
            }

            .recap th,
            .recap td {
                border: 1px solid #000 !important;
                padding: 5px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    @include('layouts.core.sidebar')
    <div class="details">
        @include('layouts.core.header')

        <div class="rsmi-content">
            <a href="{{ route('client.reports.index') }}" class="back-button">
                <ion-icon name="arrow-back-outline"></ion-icon>
                Back to Reports
            </a>

            <div class="report-header">
                <h1>Republic of the Philippines</h1>
                <h1>ATI-RTC I</h1>
                <h1>Report of Supplies and Materials Issued</h1>
                <p>For the Month of {!! isset($header['as_of']) && trim($header['as_of']) !== '' ? e($header['as_of']) : '<span style="border-bottom:1px solid #000;padding:0 80px;display:inline-block;">&nbsp;</span>' !!}</p>
                <p>Fund Cluster: {!! isset($header['fund_cluster']) && trim($header['fund_cluster']) !== '' ? e($header['fund_cluster']) : '<span style="border-bottom:1px solid #000;padding:0 40px;display:inline-block;">&nbsp;</span>' !!}</p>
            </div>

            <div class="accountability-info" style="text-align: center; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <p>
                    For which
                    {!! isset($header['accountable_person']) && trim($header['accountable_person']) !== '' ? e($header['accountable_person']) : '<span style="border-bottom:1px solid #000;padding:0 120px;display:inline-block;">&nbsp;</span>' !!},
                    {!! isset($header['position']) && trim($header['position']) !== '' ? e($header['position']) : '<span style="border-bottom:1px solid #000;padding:0 120px;display:inline-block;">&nbsp;</span>' !!},
                    {!! isset($header['office']) && trim($header['office']) !== '' ? e($header['office']) : '<span style="border-bottom:1px solid #000;padding:0 120px;display:inline-block;">&nbsp;</span>' !!}
                    is accountable, having assumed such accountability on
                    {!! isset($header['assumption_date']) && trim($header['assumption_date']) !== '' ? e(\Carbon\Carbon::parse($header['assumption_date'])->format('F d, Y')) : '<span style="border-bottom:1px solid #000;padding:0 120px;display:inline-block;">&nbsp;</span>' !!}.
                </p>
            </div>

            <div class="report-info" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <div class="header-grid" style="display:grid;grid-template-columns: repeat(4, 1fr);gap: 20px;text-align:center;">
                    <div>
                        <p style="margin:0; font-weight:600;">Entity Name:</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">
                            {!! isset($header['entity_name']) && trim($header['entity_name']) !== '' ? e($header['entity_name']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}
                        </p>
                    </div>

                    <div>
                        <p style="margin:0; font-weight:600;">Accountable Officer:</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">
                            {!! isset($header['accountable_person']) && trim($header['accountable_person']) !== '' ? e($header['accountable_person']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}
                        </p>
                        <p style="margin:4px 0 0 0; font-style:italic;">(Name)</p>
                    </div>

                    <div>
                        <p style="margin:0; font-weight:600;">Position:</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">
                            {!! isset($header['position']) && trim($header['position']) !== '' ? e($header['position']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}
                        </p>
                        <p style="margin:4px 0 0 0; font-style:italic;">(Designation)</p>
                    </div>

                    <div>
                        <p style="margin:0; font-weight:600;">Office:</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">
                            {!! isset($header['office']) && trim($header['office']) !== '' ? e($header['office']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}
                        </p>
                        <p style="margin:4px 0 0 0; font-style:italic;">(Station)</p>
                        <p style="margin:8px 0 0 0; font-weight:600;">Fund Cluster: {!! isset($header['fund_cluster']) && trim($header['fund_cluster']) !== '' ? '<span style="border-bottom:1px solid #000; padding:0 18px;">' . e($header['fund_cluster']) . '</span>' : '<span style="border-bottom:1px solid #000;padding:0 18px;display:inline-block;">&nbsp;</span>' !!}</p>
                    </div>
                </div>
            </div>

            <div class="filters-section">
                <form method="GET" action="{{ route('client.report.rsmi') }}" class="filters-form">
                    <div class="filter-group">
                        <label for="date_from">Date From</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="filter-group">
                        <label for="date_to">Date To</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="filter-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="Enter department" value="{{ request('department') }}">
                    </div>
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All</option>
                            <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('client.report.rsmi') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>

            {{-- Header input form --}}
            <div class="filters-section">
                <form method="get" class="filters-form">
                    {{-- preserve current filters as hidden inputs --}}
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">

                    <div class="filter-group">
                        <label>As of</label>
                        <input type="date" name="as_of" value="{{ request('as_of') ?? now()->format('Y-m-d') }}">
                    </div>
                    <div class="filter-group">
                        <label>Entity Name</label>
                        <input type="text" name="entity_name" value="{{ request('entity_name') ?? '' }}">
                    </div>
                    <div class="filter-group">
                        <label>Fund Cluster</label>
                        <input type="text" name="fund_cluster" value="{{ request('fund_cluster') ?? '' }}">
                    </div>
                    <div class="filter-group">
                        <label>Accountable Person</label>
                        <input type="text" name="accountable_person" value="{{ request('accountable_person') ?? '' }}">
                    </div>
                    <div class="filter-group">
                        <label>Position</label>
                        <input type="text" name="position" value="{{ request('position') ?? '' }}">
                    </div>
                    <div class="filter-group">
                        <label>Office</label>
                        <input type="text" name="office" value="{{ request('office') ?? '' }}">
                    </div>
                    <div class="filter-group">
                        <label>Assumption Date</label>
                        <input type="date" name="assumption_date" value="{{ request('assumption_date') ?? '' }}">
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">Apply Header</button>
                        <a href="{{ route('client.report.rsmi') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>

            @if($rsmiItems->count() > 0)
                <div class="report-table-container">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>RIS No.</th>
                                <th>Responsibility Center Code</th>
                                <th>Stock No.</th>
                                <th>Item</th>
                                <th>Unit</th>
                                <th>Quantity Issued</th>
                                <th>Unit Cost</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rsmiItems as $item)
                                <tr>
                                    <td>{{ $item->issue_no }}</td>
                                    <td>{{ $item->responsibility_center }}</td>
                                    <td>{{ $item->stock_no }}</td>
                                    <td>{{ $item->item }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->quantity_issued }}</td>
                                    <td>₱ {{ number_format($item->unit_cost, 2) }}</td>
                                    <td>₱ {{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="recap-container">
                    <div class="recap">
                        <p><strong>Recapitulation:</strong></p>
                        <table>
                            <thead>
                                <tr>
                                    <th>Stock No.</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recapLeft as $item)
                                    <tr>
                                        <td>{{ $item['stock_no'] }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="recap">
                        <p><strong>Recapitulation:</strong></p>
                        <table>
                            <thead>
                                <tr>
                                    <th>Unit Cost</th>
                                    <th>Total Cost</th>
                                    <th>UACS Object Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>₱ {{ number_format($recapRight['unit_cost'], 2) }}</td>
                                    <td>₱ {{ number_format($recapRight['total_cost'], 2) }}</td>
                                    <td>{{ $recapRight['uacs_code'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @include('client.report._export_fab', [
                    'excelUrl' => route('client.report.rsmi.export.excel', request()->query()),
                    'pdfUrl' => route('client.report.rsmi.export.pdf', request()->query())
                ])
            @else
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Supplies Found</h3>
                    <p>There are no supplies records to display. Try adjusting your filters or add supplies first.</p>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
