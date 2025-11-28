<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EseePark - Parking Reservation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
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
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>EseePark - Parking Reservation System</h1>
        

        <!-- Form Section -->
        <div class="form-section">
            <h2>Create Reservation</h2>
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
                    <label for="parking_no">Parking No.:</label>
                    <input type="text" id="parking_no" name="parking_no" required>
                </div>

                <div class="form-group">
                    <label for="phone_no">Phone No.:</label>
                    <input type="tel" id="phone_no" name="phone_no" required>
                </div>

                <button type="submit">Submit Reservation</button>
            </form>
        </div>

    </div>

    <script>
        document.getElementById('reservationForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                name: document.getElementById('name').value,
                plate_number: document.getElementById('plate_number').value,
                reservation_date: document.getElementById('reservation_date').value,
                reservation_time: document.getElementById('reservation_time').value,
                parking_no: document.getElementById('parking_no').value,
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
