<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EseePark - Parking Reservation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #fdf2f2, #f3f4f6);
            margin: 0;
            padding: 0;
        }
        .main {
            padding: 24px 32px;
        }
        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            padding: 24px 30px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-section {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #800000;
            box-shadow: 0 0 5px rgba(128, 0, 0, 0.3);
        }
        button {
            background-color: #800000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #a00000;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .back-link {
            display: inline-block;
            padding: 8px 16px;
            background-color: #800000;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
        }
        .back-link:hover {
            background-color: #a00000;
        }
        .logout-btn {
            background-color: #800000;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .logout-btn:hover {
            background-color: #a00000;
        }
        .app-header {
            background-color: #800000;
            color: #fff;
            padding: 12px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        }
        .app-brand {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .app-nav {
            display: flex;
            gap: 16px;
        }
        .nav-link {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 14px;
            padding: 6px 10px;
            border-radius: 4px;
        }
        .nav-link:hover, .nav-link-active {
            background-color: rgba(255,255,255,0.2);
            color: #fff;
        }

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
            <a href="{{ url('/') }}" class="nav-link">Dashboard</a>
            <a href="{{ url('/reservations') }}" class="nav-link nav-link-active">New Reservation</a>
        </nav>
    </header>

    <main class="main">
    <div class="container">
        <div class="top-bar">
            <a href="{{ url('/') }}" class="back-link">Back to Dashboard</a>

            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

        <div class="form-section">
            <h1>EseePark - Parking Reservation System</h1>
            <h2>Create Reservation</h2>
            <p style="margin-bottom:20px;color:#555;">Provide your vehicle details and preferred schedule. A parking attendant will assign the final parking slot when you arrive or when your reservation is approved.</p>
            <form id="reservationForm">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="plate_number">Plate Number:</label>
                    <input type="text" id="plate_number" name="plate_number" required>
                </div>

                <div class="form-group">
                    <label for="reservation_date">Date:</label>
                    <input type="date" id="reservation_date" name="reservation_date" required>
                </div>

                <div class="form-group">
                    <label for="reservation_time">Time:</label>
                    <input type="time" id="reservation_time" name="reservation_time" required>
                </div>

                <div class="form-group">
                    <label for="preferred_parking_no">Preferred Parking Area (optional):</label>
                    <select id="preferred_parking_no" name="preferred_parking_no">
                        <option value="">No preference</option>
                        <option value="A">Parking A</option>
                        <option value="B">Parking B</option>
                        <option value="C">Parking C</option>
                        <option value="D">Parking D</option>
                        <option value="E">Parking E</option>
                        <option value="F">Parking F</option>
                    </select>
                    <small style="color:#666;">The attendant will try to honor your preference if slots are available.</small>
                </div>

                <div class="form-group">
                    <label for="phone_no">Phone No. (optional):</label>
                    <input type="tel" id="phone_no" name="phone_no">
                </div>

                <button type="submit">Submit Reservation</button>
            </form>
        </div>

    </div>
    </main>

    <script>
        document.getElementById('reservationForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                name: document.getElementById('name').value,
                plate_number: document.getElementById('plate_number').value,
                reservation_date: document.getElementById('reservation_date').value,
                reservation_time: document.getElementById('reservation_time').value,
                preferred_parking_no: document.getElementById('preferred_parking_no').value,
                phone_no: document.getElementById('phone_no').value
            };

            try {
                const response = await fetch('/api/reservations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Reservation submitted successfully!');
                } else {
                    alert('Error: ' + data.message);
                }

                // Clear form
                document.getElementById('reservationForm').reset();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    </script>
</body>
</html>
