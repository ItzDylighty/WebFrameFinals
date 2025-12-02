<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EseePark - Walk-In Entry</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #fdf2f2, #f3f4f6); margin: 0; padding: 0; color: #111; }
        .app-header { background-color: #800000; color: #fff; padding: 12px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.25); }
        .app-brand { font-size: 20px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .app-nav { display: flex; gap: 16px; align-items: center; }
        .nav-link { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 14px; padding: 6px 10px; border-radius: 4px; }
        .nav-link:hover, .nav-link-active { background-color: rgba(255,255,255,0.2); color: #fff; }
        .nav-button { background: transparent; color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.4); border-radius: 4px; padding: 6px 12px; cursor: pointer; }
        .main { padding: 24px 32px; }
        .container { width: 100%; max-width: 900px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        h1 { margin-bottom: 6px; }
        p.description { margin-bottom: 20px; color: #555; }
        form { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: 600; margin-bottom: 6px; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        input:focus, select:focus { outline: none; border-color: #800000; box-shadow: 0 0 4px rgba(128, 0, 0, 0.3); }
        .actions { margin-top: 20px; display: flex; gap: 12px; }
        .btn { border: none; border-radius: 4px; padding: 10px 18px; font-size: 14px; cursor: pointer; }
        .btn-primary { background-color: #800000; color: #fff; }
        .btn-primary:hover { background-color: #a00000; }
        .btn-secondary { background-color: #ddd; }
        .btn-secondary:hover { background-color: #ccc; }
        .status-message { margin-top: 16px; font-weight: 600; }
        .slot-grid { margin-top: 30px; }
        .slot-grid-title { font-size: 18px; font-weight: 600; margin-bottom: 12px; }
        .slot-area { margin-bottom: 20px; }
        .slot-area h3 { margin: 0 0 8px 0; }
        .slot-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(45px, 1fr)); gap: 6px; }
        .slot-btn { border: none; border-radius: 6px; padding: 8px 0; font-size: 13px; font-weight: 600; cursor: pointer; }
        .slot-btn.status-vacant { background-color: #dcfce7; color: #14532d; }
        .slot-btn.status-reserved { background-color: #fef3c7; color: #92400e; }
        .slot-btn.status-occupied { background-color: #fee2e2; color: #991b1b; }
        .slot-btn.slot-selected { outline: 3px solid #2563eb; }
        @media (max-width: 640px) {
            form { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="app-brand">EseePark Admin</div>
        <nav class="app-nav">
            <a href="{{ url('/admin/reservations') }}" class="nav-link">Admin Dashboard</a>
            <a href="{{ url('/admin/walk-in') }}" class="nav-link nav-link-active">Walk-In Entry</a>
            <a href="{{ url('/admin/analytics') }}" class="nav-link">Analytics</a>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="nav-button">Logout</button>
            </form>
        </nav>
    </header>

    <main class="main">
        <div class="container">
            <h1>Walk-In Vehicle Entry</h1>
            <p class="description">Use this form when a vehicle arrives without a prior reservation. Choose an available slot, collect driver details, and check them in.</p>

            <div class="slot-grid" id="slotGrid"></div>

            <form id="walkInForm" style="margin-top:20px;">
                <div class="form-group">
                    <label for="selectedSlot">Selected Slot</label>
                    <input type="text" id="selectedSlot" name="selectedSlot" placeholder="Choose a slot from the grid" readonly required>
                </div>

                <div class="form-group">
                    <label for="name">Driver Name</label>
                    <input type="text" id="name" required>
                </div>

                <div class="form-group">
                    <label for="plate_number">Plate Number</label>
                    <input type="text" id="plate_number" required>
                </div>

                <div class="form-group">
                    <label for="phone_no">Phone Number (optional)</label>
                    <input type="tel" id="phone_no">
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Check In Vehicle</button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Clear</button>
                </div>
            </form>

            <div id="statusMessage" class="status-message"></div>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let parkingAreas = [];
        let selectedSlotId = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadSlots();
        });

        async function loadSlots() {
            try {
                const response = await fetch('/admin/api/slots');
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Unable to load slots');
                }
                parkingAreas = data.data || [];
                renderSlotGrid();
            } catch (error) {
                document.getElementById('slotGrid').innerHTML = `<div class="status-message" style="color:#b91c1c;">${error.message}</div>`;
            }
        }

        function renderSlotGrid() {
            const container = document.getElementById('slotGrid');
            if (!parkingAreas.length) {
                container.innerHTML = '<div class="status-message">No parking areas configured.</div>';
                return;
            }

            container.innerHTML = parkingAreas.map(area => {
                const slotsHtml = area.slots.map(slot => {
                    const selectedClass = slot.id === selectedSlotId ? 'slot-selected' : '';
                    return `<button type="button" class="slot-btn status-${slot.status} ${selectedClass}" data-slot-id="${slot.id}" ${slot.status === 'vacant' ? '' : 'disabled'}>${area.code}-${slot.slot_number}</button>`;
                }).join('');

                return `
                    <div class="slot-area">
                        <h3>${area.name} (${area.code})</h3>
                        <div class="slot-list">
                            ${slotsHtml}
                        </div>
                    </div>
                `;
            }).join('');

            container.querySelectorAll('.slot-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    selectedSlotId = Number(btn.getAttribute('data-slot-id'));
                    document.getElementById('selectedSlot').value = btn.textContent;
                    renderSlotGrid();
                });
            });
        }

        document.getElementById('walkInForm').addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!selectedSlotId) {
                alert('Please select a vacant slot first.');
                return;
            }

            const payload = {
                name: document.getElementById('name').value,
                plate_number: document.getElementById('plate_number').value,
                phone_no: document.getElementById('phone_no').value,
            };

            try {
                const response = await fetch(`/admin/api/slots/${selectedSlotId}/walk-in`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Unable to check in vehicle.');
                }

                document.getElementById('statusMessage').style.color = '#16a34a';
                document.getElementById('statusMessage').textContent = 'Vehicle checked in successfully!';
                resetForm();
                await loadSlots();
            } catch (error) {
                document.getElementById('statusMessage').style.color = '#b91c1c';
                document.getElementById('statusMessage').textContent = error.message;
            }
        });

        function resetForm() {
            selectedSlotId = null;
            document.getElementById('walkInForm').reset();
            document.getElementById('selectedSlot').value = '';
            document.getElementById('statusMessage').textContent = '';
            renderSlotGrid();
        }
    </script>
</body>
</html>
