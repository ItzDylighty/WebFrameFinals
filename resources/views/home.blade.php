<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EseePark - Home</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #fdf2f2, #f3f4f6); margin: 0; padding: 0; }
        .main { padding: 24px 32px; }
        .container { width: 100%; max-width: 1100px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        h1 { margin-bottom: 10px; }
        p { margin-bottom: 20px; color: #555; }
        .user-info { margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 4px; }
        .user-info h2 { margin-bottom: 5px; }
        .user-info span { font-weight: normal; color: #555; }
        .section-title { margin-top: 10px; margin-bottom: 10px; font-weight: bold; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 3px; border: none; cursor: pointer; font-size: 14px; text-decoration: none; text-align: center; }
        .btn-primary { background-color: #4CAF50; color: #fff; }
        .btn-primary:hover { background-color: #45a049; }
        .btn-secondary { background-color: #800000; color: #fff; }
        .btn-secondary:hover { background-color: #a00000; }
        form { margin: 0; }
        .stats { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px; margin-bottom: 20px; }
        .stat-card { flex: 1 1 150px; background-color: #f9f9f9; padding: 15px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-label { font-size: 13px; color: #777; margin-bottom: 5px; }
        .stat-value { font-size: 20px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 14px; text-align: left; }
        th { background-color: #fafafa; font-weight: bold; }
        .tabs { display: flex; gap: 8px; margin-top: 10px; border-bottom: 1px solid #ddd; }
        .tab { background-color: transparent; border: none; padding: 8px 12px; cursor: pointer; font-size: 14px; border-radius: 4px 4px 0 0; color: #555; }
        .tab.active { background-color: #fff; border: 1px solid #ddd; border-bottom: 1px solid #fff; color: #333; }
        .tab-content { display: none; padding-top: 10px; }
        .tab-content.active { display: block; }

        .app-header { background-color: #800000; color: #fff; padding: 12px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.25); }
        .app-brand { font-size: 20px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .app-nav { display: flex; gap: 16px; align-items: center; }
        .nav-link { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 14px; padding: 6px 10px; border-radius: 4px; }
        .nav-link:hover, .nav-link-active { background-color: rgba(255,255,255,0.2); color: #fff; }
        .nav-link-button { background: transparent; border: none; color: rgba(255,255,255,0.85); padding: 6px 10px; border-radius: 4px; font-size: 14px; cursor: pointer; }
        .nav-link-button:hover { background-color: rgba(255,255,255,0.2); color: #fff; }

        @media (max-width: 768px) {
            .main { padding: 16px; }
            .container { padding: 20px; }
            .app-header { padding: 10px 16px; flex-direction: column; align-items: flex-start; gap: 8px; }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="app-brand">EseePark</div>
        <nav class="app-nav">
            <a href="{{ url('/') }}" class="nav-link nav-link-active">Dashboard</a>

            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="nav-link-button">Logout</button>
            </form>
        </nav>
    </header>

    <main class="main">
        <div class="container">
        <h1>Parking Dashboard</h1>

        <div class="user-info">
            <h2>Welcome, {{ auth()->user()->name }}</h2>
            <p>Email: <span>{{ auth()->user()->email }}</span></p>
        </div>

        <p class="section-title">Quick Actions</p>
        <div class="actions">
            <a href="{{ url('/reservations') }}" class="btn btn-primary">Create a Reservation</a>
        </div>

        <p class="section-title">Reservations</p>

        <div class="tabs">
            <button type="button" class="tab active" data-tab="overview">Overview</button>
            <button type="button" class="tab" data-tab="history">History</button>
        </div>

        <div id="tab-overview" class="tab-content active">
            <p class="section-title">Your Reservation Summary</p>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-label">Total Reservations</div>
                    <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Upcoming Reservations</div>
                    <div class="stat-value">{{ $stats['upcoming'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Completed Reservations</div>
                    <div class="stat-value">{{ $stats['completed'] ?? 0 }}</div>
                </div>
            </div>

            <p class="section-title">Recent Reservations</p>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Plate No.</th>
                        <th>Parking Area</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentReservations as $reservation)
                        <tr>
                            <td>{{ optional($reservation->reservation_date)->format('Y-m-d') }}</td>
                            <td>{{ $reservation->reservation_time }}</td>
                            <td>{{ $reservation->plate_number }}</td>
                            <td>{{ $reservation->parking_no }}</td>
                            <td>
                                @php($date = optional($reservation->reservation_date)->format('Y-m-d'))
                                @if ($date >= now()->toDateString())
                                    Upcoming
                                @else
                                    Completed
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No reservations yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="tab-history" class="tab-content">
            <p class="section-title">Reservation History</p>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Plate No.</th>
                        <th>Parking Area</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allReservations as $reservation)
                        <tr>
                            <td>{{ optional($reservation->reservation_date)->format('Y-m-d') }}</td>
                            <td>{{ $reservation->reservation_time }}</td>
                            <td>{{ $reservation->plate_number }}</td>
                            <td>{{ $reservation->parking_no }}</td>
                            <td>
                                @php($date = optional($reservation->reservation_date)->format('Y-m-d'))
                                @if ($date >= now()->toDateString())
                                    Upcoming
                                @else
                                    Completed
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No reservations yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const target = tab.getAttribute('data-tab');

                    tabs.forEach(function (t) { t.classList.remove('active'); });
                    contents.forEach(function (c) { c.classList.remove('active'); });

                    tab.classList.add('active');
                    const activeContent = document.getElementById('tab-' + target);
                    if (activeContent) {
                        activeContent.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>
