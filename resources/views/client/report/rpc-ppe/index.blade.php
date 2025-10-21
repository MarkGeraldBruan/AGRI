<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPC-PPE Report</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .rpc-ppe-content {
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

        .classification-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            border: 2px solid #000;
        }

        .equipment-table th {
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            color: #000;
            border: 1px solid #000;
            background: #e8f5e9;
            font-size: 10px;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .equipment-table td {
            padding: 8px 6px;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 11px;
        }

        .classification-header-row {
            background: #b8daba !important;
        }

        .classification-header-row td {
            font-weight: bold;
            font-size: 12px;
            padding: 10px;
            text-align: center;
            color: #000;
        }

        .article-group-row {
            background: #d4edda;
        }

        .article-group-row td {
            font-weight: bold;
            padding: 8px;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .shortage-overage {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
        }

        .shortage-overage div {
            font-size: 10px;
        }

        .remarks-cell {
            font-size: 10px;
            line-height: 1.4;
        }

        .remarks-cell div {
            margin-bottom: 3px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 20px;
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
            text-decoration: none;
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

        .print-button:hover {
            background: #1e4612;
            transform: scale(1.05);
        }

        @media print {
            body {
                font-family: 'DejaVu Sans Custom', 'DejaVu Sans', 'Times New Roman', serif;
                font-size: 10px;
                line-height: 1.4;
                margin: 0;
                padding: 10px;
            }

            /* Show container and content */
            .container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .details {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .rpc-ppe-content {
                padding: 10px !important;
                margin: 0 !important;
            }

            /* Show report info but hide header grid for print */
            .report-info {
                display: block !important;
                background: white !important;
                padding: 10px !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                margin-bottom: 10px !important;
                page-break-after: avoid;
            }

            .header-grid {
                display: none !important;
            }

            /* Show accountability info for print */
            .accountability-info {
                display: block !important;
                text-align: center;
                margin: 20px 0;
                padding: 15px;
                background: white !important;
                border-radius: 0 !important;
                page-break-after: avoid;
            }

            .accountability-info p {
                margin: 0;
                font-size: 12px;
                color: #000;
            }

            .back-button, .filters-section, .print-button, .sidebar, .header, .logo, .system-title, .profile-pic, .user-profile, .brand-logo, .institute-name, .nav-icons, .export-fab, .fab, .navbar, .brand, .title-box, .header-container, .institute-title, .export-buttons,
            .dashboard-header, .header-left, .header-right, .navigation, .brand-container, .notifications, .user-avatar, .user-info, .fab-print, .fab-pdf, .fab-excel, .sidebar *, .header *, .dashboard-header *, .header-left *, .header-right *, .navigation *, .notifications *, .user-profile *, .user-avatar *, .user-info *, .fab *, .fab-print *, .fab-pdf *, .fab-excel * {
                display: none !important;
            }

            .report-header {
                text-align: center;
                margin-bottom: 15px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }

            .report-header h1 {
                font-size: 14px;
                font-weight: bold;
                margin: 0;
                text-transform: uppercase;
            }

            .report-header p {
                font-size: 11px;
                margin: 5px 0;
            }

            .equipment-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 8px;
                margin-bottom: 20px;
                border: 1px solid #000;
            }

            .equipment-table th {
                padding: 4px 2px;
                text-align: center;
                font-weight: bold;
                border: 1px solid #000;
                background: #f0f0f0;
                font-size: 7px;
                text-transform: uppercase;
            }

            .equipment-table td {
                padding: 4px 2px;
                border: 1px solid #000;
                font-size: 8px;
            }

            .classification-header-row {
                background: #e0e0e0 !important;
            }

            .classification-header-row td {
                font-weight: bold;
                font-size: 9px;
                padding: 6px 2px;
                text-align: center;
            }

            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            .page-break {
                page-break-before: always;
            }

            .classification-section {
                page-break-inside: avoid;
            }

            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @include('layouts.core.sidebar')
        <div class="details">
            @include('layouts.core.header')
            
            <div class="rpc-ppe-content">
                <a href="{{ route('client.reports.index') }}" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Reports
                </a>

                <div class="report-header">
                    <h1>REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</h1>
                    <p style="font-size: 12px; margin-top: 5px;">As of {!! isset($header['as_of']) && trim($header['as_of']) !== '' ? e($header['as_of']) : '<span style="border-bottom:1px solid #000;padding:0 50px;display:inline-block;">&nbsp;</span>' !!}</p>
                </div>

                {{-- Header display section --}}
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

                    {{-- Accountability text --}}
                    <div class="accountability-info" style="text-align: center; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <p>
                            For which
                            {!! isset($header['accountable_person']) && trim($header['accountable_person']) !== '' ? e($header['accountable_person']) : '<span style="border-bottom:1px solid #000;padding:0 90px;display:inline-block;">&nbsp;</span>' !!},
                            {!! isset($header['position']) && trim($header['position']) !== '' ? e($header['position']) : '<span style="border-bottom:1px solid #000;padding:0 90px;display:inline-block;">&nbsp;</span>' !!},
                            {!! isset($header['office']) && trim($header['office']) !== '' ? e($header['office']) : '<span style="border-bottom:1px solid #000;padding:0 90px;display:inline-block;">&nbsp;</span>' !!}
                            is accountable, having assumed such accountability on
                            {!! isset($header['assumption_date']) && trim($header['assumption_date']) !== '' ? e(\Carbon\Carbon::parse($header['assumption_date'])->format('F d, Y')) : '<span style="border-bottom:1px solid #000;padding:0 90px;display:inline-block;">&nbsp;</span>' !!}.
                        </p>
                    </div>
                </div>

                {{-- Data filters --}}
                <div class="filters-section">
                    <form method="GET" action="{{ route('client.report.rpc-ppe') }}" class="filters-form">
                        <div class="filter-group">
                            <label>Classification</label>
                            <select name="classification">
                                <option value="">All Classifications</option>
                                @foreach($classifications as $class)
                                    <option value="{{ $class }}" {{ request('classification') == $class ? 'selected' : '' }}>
                                        {{ $class }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Condition</label>
                            <select name="condition">
                                <option value="">All Conditions</option>
                                <option value="Serviceable" {{ request('condition') == 'Serviceable' ? 'selected' : '' }}>Serviceable</option>
                                <option value="Unserviceable" {{ request('condition') == 'Unserviceable' ? 'selected' : '' }}>Unserviceable</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}">
                        </div>

                        <div class="filter-group">
                            <label>Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}">
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="{{ route('client.report.rpc-ppe') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Header input form --}}
                <div class="filters-section" style="margin-top: 10px; background: #f8f9fa; border: 1px solid #ddd;">
                    <form method="get" class="filters-form">
                        {{-- preserve current filters as hidden inputs --}}
                        <input type="hidden" name="classification" value="{{ request('classification') }}">
                        <input type="hidden" name="condition" value="{{ request('condition') }}">
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">

                        <div class="filter-group">
                            <label>As of</label>
                            <input type="date" name="as_of" value="{{ request('as_of') }}">
                        </div>
                        <div class="filter-group">
                            <label>Entity Name</label>
                            <input type="text" name="entity_name" value="{{ request('entity_name') }}">
                        </div>
                        <div class="filter-group">
                            <label>Fund Cluster</label>
                            <input type="text" name="fund_cluster" value="{{ request('fund_cluster') }}">
                        </div>
                        <div class="filter-group">
                            <label>Accountable Person</label>
                            <input type="text" name="accountable_person" value="{{ request('accountable_person') }}">
                        </div>
                        <div class="filter-group">
                            <label>Position</label>
                            <input type="text" name="position" value="{{ request('position') }}">
                        </div>
                        <div class="filter-group">
                            <label>Office</label>
                            <input type="text" name="office" value="{{ request('office') }}">
                        </div>
                        <div class="filter-group">
                            <label>Assumption Date</label>
                            <input type="date" name="assumption_date" value="{{ request('assumption_date') }}">
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary">Apply Header</button>
                            <a href="{{ route('client.report.rpc-ppe') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Equipment Report Table -->
                @if($groupedEquipment->count() > 0)
                    <div class="classification-section">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 12%;">ARTICLE/ITEM</th>
                                    <th rowspan="2" style="width: 15%;">DESCRIPTION</th>
                                    <th rowspan="2" style="width: 10%;">PROPERTY NUMBER</th>
                                    <th rowspan="2" style="width: 8%;">UNIT OF MEASURE</th>
                                    <th rowspan="2" style="width: 8%;">UNIT VALUE</th>
                                    <th rowspan="2" style="width: 8%;">Acquisition<br>Date</th>
                                    <th rowspan="2" style="width: 6%;">QUANTITY per<br>PROPERTY CARD</th>
                                    <th rowspan="2" style="width: 6%;">QUANTITY per<br>PHYSICAL COUNT</th>
                                    <th colspan="2" style="width: 10%;">SHORTAGE/OVERAGE</th>
                                    <th colspan="3" style="width: 17%;">REMARKS</th>
                                </tr>
                                <tr>
                                    <th style="width: 5%;">Quantity</th>
                                    <th style="width: 5%;">Value</th>
                                    <th style="width: 6%;">Person<br>Responsible</th>
                                    <th style="width: 6%;">Responsibility<br>Center</th>
                                    <th style="width: 5%;">Condition of<br>Properties</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedEquipment as $classification => $equipmentItems)
                                    <!-- Classification Header -->
                                    <tr class="classification-header-row">
                                        <td colspan="13">
                                            {{ strtoupper($classification ?: 'UNCLASSIFIED EQUIPMENT') }}
                                        </td>
                                    </tr>

                                    @php
                                        $groupedByArticle = $equipmentItems->groupBy('article');
                                    @endphp

                                    @foreach($groupedByArticle as $article => $items)
                                        @foreach($items as $index => $equipment)
                                            <tr>
                                                @if($index === 0)
                                                    <!-- Show article name only for first item -->
                                                    <td rowspan="{{ $items->count() }}" style="vertical-align: middle; font-weight: bold;">
                                                        {{ $article }}
                                                    </td>
                                                @endif

                                                <td>{{ $equipment->description ?: '-' }}</td>
                                                <td class="text-center">{{ $equipment->property_number }}</td>
                                                <td class="text-center">{{ $equipment->unit_of_measurement }}</td>
                                                <td class="text-right">₱ {{ number_format($equipment->unit_value, 2) }}</td>
                                                <td class="text-center">
                                                    {{ $equipment->acquisition_date ? $equipment->acquisition_date->format('M-d') : '-' }}
                                                </td>
                                                <td class="text-center">1</td>
                                                <td class="text-center">1</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center remarks-cell">
                                                    {{ $equipment->responsible_person ?: 'Unknown / Book of the Accountant' }}
                                                </td>
                                                <td class="text-center remarks-cell">
                                                    {{ $equipment->location ?: '-' }}
                                                </td>
                                                <td class="text-center">{{ $equipment->condition }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                        @include('client.report._export_fab', [
                            'excelUrl' => route('client.report.rpc-ppe.export.excel', request()->query()),
                            'pdfUrl' => route('client.report.rpc-ppe.export.pdf', request()->query())
                        ])
                @else
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No Equipment Found</h3>
                        <p>There are no equipment records to display. Try adjusting your filters or add equipment first.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
