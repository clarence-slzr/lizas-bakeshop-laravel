@extends('layouts.app')

@section('content')
    <div class="shift-container">

        <!-- ============ PAGE HEADER ============ -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Shift Management</h1>
                <p class="page-description">Track your work shifts and cash drawer balance</p>
            </div>
            @if($activeShift)
                <div class="status-pill status-active">
                    <span class="status-dot"></span> Active Shift
                </div>
            @else
                <div class="status-pill status-inactive">
                    <span class="status-dot"></span> No Active Shift
                </div>
            @endif
        </div>

        <!-- ============ FLASH MESSAGES ============ -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                @if(session('success') === 'started')
                    Shift started successfully!
                @elseif(session('success') === 'ended')
                    Shift ended successfully!
                @else
                    {{ session('success') }}
                @endif
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                @if(session('error') === 'already_active')
                    You already have an active shift.
                @elseif(session('error') === 'no_active')
                    No active shift found.
                @else
                    {{ session('error') }}
                @endif
            </div>
        @endif

        <!-- ============ MAIN GRID ============ -->
        <div class="main-grid">

            <!-- LEFT: SHIFT ACTION -->
            <div class="action-card">
                <div class="action-card-header">
                    <div class="header-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <h3>{{ $activeShift ? 'Current Shift' : 'Start New Shift' }}</h3>
                        <p>{{ $activeShift ? 'You have an ongoing shift' : 'Begin your shift by entering starting cash' }}</p>
                    </div>
                </div>

                @if($activeShift)
                    <div class="action-card-body">
                        <div class="active-shift-info">
                            <div class="active-icon-wrap">
                                <i class="fas fa-play-circle"></i>
                            </div>
                            <h2>Shift in Progress</h2>
                            <p class="active-subtitle">Nag-start ka noong</p>
                            <p class="active-time">
                                {{ \Carbon\Carbon::parse($activeShift->shift_start)->format('l, F j, Y — h:i A') }}
                            </p>

                            <div class="active-stats">
                                <div class="active-stat-item">
                                    <span class="active-stat-label">Starting Cash</span>
                                    <span class="active-stat-value">₱{{ number_format($activeShift->starting_cash ?? 0, 2) }}</span>
                                </div>
                                <div class="active-stat-divider"></div>
                                <div class="active-stat-item">
                                    <span class="active-stat-label">Live Sales</span>
                                    <span class="active-stat-value">₱{{ number_format($liveTotal ?? 0, 2) }}</span>
                                </div>
                            </div>

                            <div class="active-stats">
                                <div class="active-stat-item">
                                    <span class="active-stat-label">Live Orders</span>
                                    <span class="active-stat-value">{{ number_format($liveOrders ?? 0) }}</span>
                                </div>
                                <div class="active-stat-divider"></div>
                                <div class="active-stat-item">
                                    <span class="active-stat-label">Expected Cash</span>
                                    <span class="active-stat-value">₱{{ number_format($expectedCash ?? 0, 2) }}</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('cashier.shift.end') }}" class="end-shift-form">
                                @csrf
                                <div class="form-group">
                                    <label for="ending_cash">Ending Cash (Actual Count)</label>
                                    <div class="input-with-icon">
                                        <span class="currency-symbol">₱</span>
                                        <input type="number" name="ending_cash" id="ending_cash" step="0.01" min="0"
                                            placeholder="0.00" required autofocus>
                                    </div>
                                    <small class="form-help">Bilangin ang laman ng cash drawer para sa accurate reconciliation.</small>
                                </div>

                                <div class="form-group">
                                    <label for="shift_notes">Notes (Optional)</label>
                                    <div class="input-with-icon">
                                        <textarea name="shift_notes" id="shift_notes" rows="2" placeholder="Anumang notes para sa shift na ito..."
                                            style="width: 100%; padding: 0.75rem; border: 1.5px solid #E3DCD0; border-radius: 0.5rem; font-family: inherit; font-size: 0.9rem; resize: vertical;"></textarea>
                                    </div>
                                </div>

                                <button type="submit" class="btn-action btn-end"
                                    onclick="return confirm('Are you sure you want to end your shift?')">
                                    <i class="fas fa-stop-circle"></i> End Shift
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="action-card-body">
                        <div class="start-shift-info">
                            <div class="start-icon-wrap">
                                <i class="fas fa-play"></i>
                            </div>
                            <h2>Start New Shift</h2>
                            <p class="start-subtitle">Begin your shift by entering the starting cash in the drawer.</p>

                            <form method="POST" action="{{ route('cashier.shift.start') }}" class="start-shift-form">
                                @csrf
                                <div class="form-group">
                                    <label for="starting_cash">Starting Cash (Float)</label>
                                    <div class="input-with-icon">
                                        <span class="currency-symbol">₱</span>
                                        <input type="number" name="starting_cash" id="starting_cash" step="0.01"
                                            min="0" placeholder="0.00" required autofocus>
                                    </div>
                                    <small class="form-help">Enter the initial cash amount in the drawer.</small>
                                </div>

                                <button type="submit" class="btn-action btn-start">
                                    <i class="fas fa-play"></i> Start Shift
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- RIGHT: SHIFT OVERVIEW -->
            <div class="overview-card">
                <div class="overview-header">
                    <div class="header-icon">
                        <i class="fas fa-chart-simple"></i>
                    </div>
                    <div>
                        <h3>Shift Overview</h3>
                        <p>Your all-time shift statistics</p>
                    </div>
                </div>

                <div class="overview-body">
                    <div class="overview-item">
                        <div class="overview-icon overview-icon-blue">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                        <div class="overview-info">
                            <span class="overview-value">{{ number_format($totalShifts ?? 0) }}</span>
                            <span class="overview-label">Total Shifts</span>
                        </div>
                    </div>

                    <div class="overview-item">
                        <div class="overview-icon overview-icon-green">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                        <div class="overview-info">
                            <span class="overview-value">₱{{ number_format($totalSalesAll ?? 0, 2) }}</span>
                            <span class="overview-label">Total Sales</span>
                        </div>
                    </div>

                    <div class="overview-item">
                        <div class="overview-icon overview-icon-olive">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="overview-info">
                            <span class="overview-value">₱{{ number_format($avgSalesPerShift ?? 0, 2) }}</span>
                            <span class="overview-label">Avg Per Shift</span>
                        </div>
                    </div>

                    <div class="overview-item overview-item-highlight">
                        <div class="overview-icon overview-icon-gold">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="overview-info">
                            <span class="overview-value">₱{{ number_format($bestShift ?? 0, 2) }}</span>
                            <span class="overview-label">Best Shift</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SHIFT HISTORY -->
        <div class="history-card">
            <div class="history-header">
                <div class="history-header-left">
                    <div class="header-icon">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3>Shift History</h3>
                        <p>Recent shifts you've completed</p>
                    </div>
                    <span class="history-badge">Last {{ $shifts->count() }} shift{{ $shifts->count() != 1 ? 's' : '' }}</span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Start Cash</th>
                            <th>Orders</th>
                            <th>Sales</th>
                            <th>End Cash</th>
                            <th>Variance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($shifts->count() > 0)
                            @foreach($shifts as $shift)
                                @php
                                    $start = \Carbon\Carbon::parse($shift->shift_start);
                                    $end = $shift->shift_end ? \Carbon\Carbon::parse($shift->shift_end) : null;

                                    // Duration with seconds
                                    $durationText = null;
                                    if ($end) {
                                        $totalSeconds = abs($end->diffInSeconds($start));
                                        $hours = floor($totalSeconds / 3600);
                                        $minutes = floor(($totalSeconds % 3600) / 60);
                                        $seconds = $totalSeconds % 60;

                                        if ($hours > 0) {
                                            $durationText = $hours . 'h ' . $minutes . 'm';
                                        } elseif ($minutes > 0) {
                                            $durationText = $minutes . 'm ' . $seconds . 's';
                                        } else {
                                            $durationText = $seconds . 's';
                                        }
                                    }

                                    // Variance: actual vs expected
                                    $variance = null;
                                    if ($end && $shift->ending_cash !== null && $shift->starting_cash !== null) {
                                        $expectedCashShift = ($shift->starting_cash ?? 0) + ($shift->total_sales ?? 0);
                                        $variance = ($shift->ending_cash ?? 0) - $expectedCashShift;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <span class="date-primary">{{ $start->format('M d, Y') }}</span>
                                        <span class="date-time">
                                            {{ $start->format('h:i A') }}
                                            @if($end) → {{ $end->format('h:i A') }} @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="duration-badge">
                                            @if($durationText)
                                                {{ $durationText }}
                                            @else
                                                <span class="text-active">Ongoing</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-amount">₱{{ number_format($shift->starting_cash ?? 0, 2) }}</td>
                                    <td class="text-center">{{ number_format($shift->order_count ?? 0) }}</td>
                                    <td class="text-amount-strong">₱{{ number_format($shift->total_sales ?? 0, 2) }}</td>
                                    <td class="text-amount">₱{{ number_format($shift->ending_cash ?? 0, 2) }}</td>
                                    <td>
                                        @if($variance !== null)
                                            <span class="variance-badge {{ $variance >= 0 ? 'variance-positive' : 'variance-negative' }}">
                                                {{ $variance >= 0 ? '+' : '' }}₱{{ number_format($variance, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($shift->shift_end)
                                            <span class="status-badge status-closed">
                                                <i class="fas fa-lock"></i> Closed
                                            </span>
                                        @else
                                            <span class="status-badge status-active">
                                                <i class="fas fa-circle"></i> Active
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="empty-row">
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-clock-rotate-left"></i>
                                        </div>
                                        <p>No shift history yet</p>
                                        <small>Your completed shifts will appear here</small>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .shift-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .page-header-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.35rem;
        }

        .page-description {
            font-size: 0.85rem;
            color: #9E9D97;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pill.status-active {
            background: #E8F0E3;
            color: #3E4A28;
            border: 1px solid #C5D4A8;
        }

        .status-pill.status-inactive {
            background: #FEF0ED;
            color: #A85444;
            border: 1px solid #E8C5BD;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .alert {
            padding: 0.875rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #E8F0E3;
            color: #3E4A28;
            border: 1px solid #C5D4A8;
        }

        .alert-error {
            background: #FEF0ED;
            color: #A85444;
            border: 1px solid #E8C5BD;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 900px) {
            .main-grid { grid-template-columns: 1fr; }
        }

        .action-card,
        .overview-card,
        .history-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .action-card-header,
        .overview-header {
            background: #FDF8F0;
            padding: 1.25rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .header-icon {
            width: 40px;
            height: 40px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #576238;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .action-card-header h3,
        .overview-header h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0 0 0.15rem 0;
        }

        .action-card-header p,
        .overview-header p {
            font-size: 0.75rem;
            color: #9E9D97;
            margin: 0;
        }

        .action-card-body {
            padding: 2rem 1.5rem;
        }

        .start-shift-info,
        .active-shift-info {
            text-align: center;
            max-width: 420px;
            margin: 0 auto;
        }

        .start-icon-wrap {
            width: 80px;
            height: 80px;
            background: #E8F0E3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            color: #576238;
            font-size: 1.75rem;
        }

        .active-icon-wrap {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #576238, #7A8B4F);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 8px 24px rgba(87, 98, 56, 0.3);
        }

        .start-shift-info h2,
        .active-shift-info h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.5rem;
        }

        .start-subtitle,
        .active-subtitle {
            font-size: 0.85rem;
            color: #9E9D97;
            margin-bottom: 1.75rem;
        }

        .active-time {
            font-size: 1rem;
            color: #576238;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .active-stats {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            padding: 1.25rem;
            background: #FDF8F0;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #F0EADC;
        }

        .active-stat-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .active-stat-label {
            font-size: 0.7rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .active-stat-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #2C2B26;
        }

        .active-stat-divider {
            width: 1px;
            height: 32px;
            background: #E3DCD0;
        }

        .form-group {
            text-align: left;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }

        .currency-symbol {
            position: absolute;
            left: 1rem;
            color: #576238;
            font-weight: 600;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .input-with-icon input {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.5rem;
            border: 1.5px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-family: inherit;
            color: #2C2B26;
            background: white;
            transition: all 0.2s ease;
        }

        .input-with-icon input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 4px rgba(87, 98, 56, 0.1);
        }

        .form-help {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.7rem;
            color: #9E9D97;
            line-height: 1.4;
        }

        .btn-action {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.9rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            color: white;
        }

        .btn-start {
            background: #576238;
        }

        .btn-start:hover {
            background: #3E4A28;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(87, 98, 56, 0.3);
        }

        .btn-end {
            background: #C5705A;
        }

        .btn-end:hover {
            background: #A85444;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(197, 112, 90, 0.3);
        }

        .overview-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .overview-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #FDF8F0;
            border: 1px solid #F0EADC;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }

        .overview-item:hover {
            border-color: #576238;
            transform: translateX(3px);
        }

        .overview-item-highlight {
            background: linear-gradient(135deg, #FEF5E8 0%, #FDF8F0 100%);
            border-color: #F8E5C5;
        }

        .overview-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .overview-icon-blue { background: #E8F0E3; color: #576238; }
        .overview-icon-green { background: #E8F0E3; color: #3E4A28; }
        .overview-icon-olive { background: #F0EADC; color: #576238; }
        .overview-icon-gold { background: #FEF5E8; color: #D4A054; }

        .overview-info {
            flex: 1;
            min-width: 0;
        }

        .overview-value {
            display: block;
            font-size: 1.15rem;
            font-weight: 700;
            color: #2C2B26;
            font-family: 'Playfair Display', serif;
            line-height: 1.2;
        }

        .overview-label {
            display: block;
            font-size: 0.7rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.15rem;
            font-weight: 500;
        }

        .history-header {
            background: #FDF8F0;
            padding: 1.25rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .history-header-left {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .history-header h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0 0 0.15rem 0;
        }

        .history-header p {
            font-size: 0.75rem;
            color: #9E9D97;
            margin: 0;
        }

        .history-badge {
            background: #F0EADC;
            color: #576238;
            padding: 0.25rem 0.7rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.25rem;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Inter', sans-serif;
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        .history-table thead tr {
            background: #FDF8F0;
            border-bottom: 1px solid #E3DCD0;
        }

        .history-table th {
            padding: 0.875rem 1.25rem;
            text-align: left;
            font-weight: 600;
            color: #576238;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .history-table td {
            padding: 1rem 1.25rem;
            text-align: left;
            color: #2C2B26;
            border-bottom: 1px solid #F0EADC;
            vertical-align: middle;
        }

        .history-table tbody tr:hover {
            background: #FDF8F0;
        }

        .date-primary {
            display: block;
            font-size: 0.8rem;
            color: #2C2B26;
            font-weight: 600;
        }

        .date-time {
            display: block;
            font-size: 0.68rem;
            color: #9E9D97;
            margin-top: 0.15rem;
        }

        .duration-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            background: #F0EADC;
            color: #576238;
            border-radius: 2rem;
            font-size: 0.72rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .text-active { color: #D4A054; font-weight: 700; }
        .text-amount { font-weight: 600; color: #2C2B26; }
        .text-amount-strong { font-weight: 700; color: #576238; }
        .text-center { text-align: center; }
        .text-muted { color: #C4C3BC; }

        .variance-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 2rem;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .variance-positive { background: #E8F0E3; color: #3E4A28; }
        .variance-negative { background: #FEF0ED; color: #A85444; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.7rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-closed { background: #F0EADC; color: #6B6A65; }
        .status-active { background: #E8F0E3; color: #3E4A28; }

        .status-active i {
            font-size: 0.5rem;
            animation: pulse 2s ease-in-out infinite;
        }

        .empty-row td { padding: 0 !important; }

        .empty-state {
            text-align: center;
            padding: 3.5rem 2rem;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: #F0EADC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .empty-icon i {
            font-size: 2rem;
            color: #C4C3BC;
        }

        .empty-state p {
            color: #6B6A65;
            margin-bottom: 0.35rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .empty-state small {
            color: #C4C3BC;
            font-size: 0.75rem;
        }

        @media (max-width: 768px) {
            .history-header { flex-direction: column; align-items: stretch; }
            .history-header-left { flex-wrap: wrap; }
            .action-card-body { padding: 1.5rem 1rem; }
            .active-stats { flex-direction: column; gap: 1rem; }
            .active-stat-divider { width: 100%; height: 1px; }
        }
    </style>
@endsection