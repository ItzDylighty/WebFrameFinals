<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EseePark - Admin Reservations</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #fdf2f2, #f3f4f6); margin: 0; padding: 0; color: #111; }
        .main { padding: 24px 32px; }
        .container { width: 100%; max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        h1 { margin-bottom: 6px; }
        p.description { margin-bottom: 20px; color: #555; }
        .app-header { background-color: #800000; color: #fff; padding: 12px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.25); }
        .app-brand { font-size: 20px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .app-nav { display: flex; gap: 16px; align-items: center; }
        .nav-link { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 14px; padding: 6px 10px; border-radius: 4px; }
        .nav-link:hover, .nav-link-active { background-color: rgba(255,255,255,0.2); color: #fff; }
        .nav-button { background: transparent; color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.4); border-radius: 4px; padding: 6px 12px; cursor: pointer; }
        .nav-button:hover { background-color: rgba(255,255,255,0.2); color: #fff; }

        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px; margin: 24px 0; }
        .stat-card { padding: 18px; border-radius: 8px; background: #f9fafb; border: 1px solid #ececec; }
        .stat-label { font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 0.08em; }
        .stat-value { font-size: 28px; font-weight: 700; margin-top: 4px; color: #111; }

        .panel { margin-top: 32px; }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .panel-title { font-size: 18px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        th, td { padding: 12px 14px; border-bottom: 1px solid #f0f0f0; font-size: 14px; text-align: left; }
        th { background-color: #fafafa; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.06em; color: #555; }
        tr:last-child td { border-bottom: none; }
        .table-empty { padding: 16px; text-align: center; color: #777; }

        .status-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-approved { background-color: #dcfce7; color: #166534; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }

        .actions { display: flex; gap: 8px; }
        .btn { border: none; border-radius: 4px; padding: 8px 12px; font-size: 13px; cursor: pointer; transition: transform 0.1s ease, opacity 0.1s ease; }
        .btn-approve { background-color: #800000; color: #fff; }
        .btn-approve:hover { background-color: #a00000; }
        .btn-reject { background-color: #800000; color: #fff; }
        .btn-reject:hover { background-color: #a00000; }
        .btn:disabled { opacity: 0.7; cursor: not-allowed; }

        .badge-light { padding: 4px 8px; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }

        .slot-panel { background: #fff9f5; }
        .slot-grid { display: grid; gap: 16px; }
        .area-card { border: 1px solid #f1f5f9; border-radius: 8px; padding: 14px; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .area-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .area-header h3 { margin: 0; font-size: 16px; }
        .slot-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); gap: 6px; }
        .slot-btn { border: none; border-radius: 6px; padding: 8px 0; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform 0.1s ease, opacity 0.1s ease; }
        .slot-btn.status-vacant { background-color: #dcfce7; color: #14532d; }
        .slot-btn.status-reserved { background-color: #fef3c7; color: #92400e; }
        .slot-btn.status-occupied { background-color: #fee2e2; color: #991b1b; }
        .slot-btn.slot-selected { outline: 3px solid #800000; }
        .slot-btn:disabled { opacity: 0.7; cursor: not-allowed; }
        .slot-details { margin-top: 16px; background: #fff; border: 1px solid #f1f5f9; border-radius: 8px; padding: 16px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.03); }
        .slot-details h4 { margin: 0 0 8px 0; }
        .slot-details p { margin: 4px 0; color: #4b5563; }
        .slot-actions { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 10px; }
        .slot-legend { margin-top: 12px; display: flex; gap: 16px; flex-wrap: wrap; font-size: 13px; color: #555; }
        .legend-pill { display: inline-flex; align-items: center; gap: 6px; }
        .legend-pill .legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
        .legend-dot.status-vacant { background-color: #bbf7d0; }
        .legend-dot.status-reserved { background-color: #fde68a; }
        .legend-dot.status-occupied { background-color: #fecaca; }

        .actions-column { display: flex; flex-direction: column; gap: 6px; }
        .btn-assign { background-color: #800000; color: #fff; }
        .btn-assign:hover { background-color: #a00000; }
        .btn-secondary-outline { background: transparent; border: 1px solid rgba(128,0,0,0.4); color: #800000; }
        .btn-secondary-outline:hover { background-color: #f8e6e6; }

        @media (max-width: 1024px) {
            .slot-list { grid-template-columns: repeat(auto-fill, minmax(36px, 1fr)); }
        }

        @media (max-width: 900px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            tr { margin-bottom: 15px; border: 1px solid #eee; border-radius: 6px; }
            td { display: flex; justify-content: space-between; align-items: center; }
            td::before { content: attr(data-label); flex: 0 0 140px; font-weight: 600; color: #555; text-transform: uppercase; letter-spacing: 0.06em; }
            .actions { justify-content: flex-end; }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="app-brand">EseePark Admin</div>
        <nav class="app-nav">
            <a href="{{ url('/') }}" class="nav-link">User Dashboard</a>
            <a href="{{ url('/admin/reservations') }}" class="nav-link nav-link-active">Admin Reservations</a>
            <a href="{{ url('/admin/walk-in') }}" class="nav-link">Walk-In Entry</a>
            <a href="{{ url('/admin/analytics') }}" class="nav-link">Analytics</a>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="nav-button">Logout</button>
            </form>
        </nav>
    </header>

    <main class="main">
        <div class="container">
            <div>
                <h1>Reservation Control Center</h1>
                <p class="description">Review all reservation requests and decide whether to approve or reject them.</p>
            </div>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value" id="statPending">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Approved</div>
                    <div class="stat-value" id="statApproved">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value" id="statRejected">0</div>
                </div>
            </div>

            <section class="panel slot-panel">
                <div class="panel-header">
                    <span class="panel-title">Live Slot Map</span>
                    <button type="button" class="btn btn-secondary-outline" onclick="loadSlots()">Refresh Slots</button>
                </div>
                <p>Select a parking slot below to view details, assign pending reservations, check in walk-ins, or vacate when vehicles leave.</p>
                <div class="slot-legend">
                    <span class="legend-pill"><span class="legend-dot status-vacant"></span> Vacant</span>
                    <span class="legend-pill"><span class="legend-dot status-reserved"></span> Reserved</span>
                    <span class="legend-pill"><span class="legend-dot status-occupied"></span> Occupied</span>
                </div>
                <div id="slotGrid" class="slot-grid" style="margin-top:16px;"></div>
                <div class="slot-details" id="slotDetails">
                    Select a slot to view its assignment and available actions.
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <span class="panel-title">Pending Reservations</span>
                    <span class="badge-light" id="pendingBadge">0 awaiting review</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Plate</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Preferred</th>
                                <th>Slot</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pendingTable">
                            <tr><td colspan="8" class="table-empty">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <span class="panel-title">Reservation History</span>
                    <span class="badge-light" id="historyBadge">0 total</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Plate</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Slot</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="historyTable">
                            <tr><td colspan="6" class="table-empty">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const pendingTable = document.getElementById('pendingTable');
        const historyTable = document.getElementById('historyTable');
        const slotGrid = document.getElementById('slotGrid');
        const slotDetails = document.getElementById('slotDetails');
        const statPending = document.getElementById('statPending');
        const statApproved = document.getElementById('statApproved');
        const statRejected = document.getElementById('statRejected');
        const pendingBadge = document.getElementById('pendingBadge');
        const historyBadge = document.getElementById('historyBadge');
        const walkInUrl = "{{ url('/admin/walk-in') }}";

        const statusClass = {
            pending: 'status-pending',
            approved: 'status-approved',
            rejected: 'status-rejected'
        };

        let reservations = [];
        let parkingAreas = [];
        let selectedSlotId = null;

        window.assignReservation = assignReservation;
        window.rejectReservation = rejectReservation;
        window.checkInSelectedSlot = checkInSelectedSlot;
        window.vacateSelectedSlot = vacateSelectedSlot;

        document.addEventListener('DOMContentLoaded', () => {
            loadData();
        });

        function badge(status) {
            const cls = statusClass[status] ?? 'status-pending';
            const label = status.charAt(0).toUpperCase() + status.slice(1);
            return `<span class="status-badge ${cls}">${label}</span>`;
        }

        function format(value) {
            return value ?? '—';
        }

        async function loadData() {
            await Promise.all([loadReservations(), loadSlots()]);
        }

        async function loadReservations() {
            try {
                const response = await fetch('/admin/api/reservations');
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to load reservations');
                }
                reservations = data.data || [];
                renderTables(reservations);
            } catch (error) {
                pendingTable.innerHTML = `<tr><td colspan="8" class="table-empty">${error.message}</td></tr>`;
                historyTable.innerHTML = `<tr><td colspan="6" class="table-empty">${error.message}</td></tr>`;
            }
        }

        async function loadSlots() {
            try {
                const response = await fetch('/admin/api/slots');
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to load slots');
                }
                parkingAreas = data.data || [];
                renderSlots();
                updateSlotDetails();
            } catch (error) {
                if (slotGrid) {
                    slotGrid.innerHTML = `<div class="table-empty">${error.message}</div>`;
                }
                if (slotDetails) {
                    slotDetails.textContent = error.message;
                }
            }
        }

        function renderTables(list) {
            const pending = list.filter(r => r.status === 'pending');
            const approved = list.filter(r => r.status === 'approved');
            const rejected = list.filter(r => r.status === 'rejected');

            statPending.textContent = pending.length;
            statApproved.textContent = approved.length;
            statRejected.textContent = rejected.length;
            pendingBadge.textContent = `${pending.length} awaiting review`;
            historyBadge.textContent = `${list.length} total`;

            pendingTable.innerHTML = pending.length === 0
                ? '<tr><td colspan="8" class="table-empty">No pending reservations.</td></tr>'
                : pending.map(pendingRow).join('');

            historyTable.innerHTML = list.length === 0
                ? '<tr><td colspan="6" class="table-empty">No reservations yet.</td></tr>'
                : list.map(historyRow).join('');
        }

        function pendingRow(reservation) {
            const user = reservation.user;
            return `
                <tr>
                    <td data-label="Driver">
                        <div><strong>${user?.name || reservation.name}</strong></div>
                        <div style="font-size:12px;color:#6b7280;">${user?.email || '—'}</div>
                    </td>
                    <td data-label="Plate">${format(reservation.plate_number)}</td>
                    <td data-label="Date">${format(reservation.reservation_date)}</td>
                    <td data-label="Time">${format(reservation.reservation_time)}</td>
                    <td data-label="Preferred">${format(reservation.preferred_parking_no) || 'Any'}</td>
                    <td data-label="Slot">${formatAssignedSlot(reservation)}</td>
                    <td data-label="Status">${badge(reservation.status)}</td>
                    <td data-label="Actions">
                        <div class="actions-column">
                            <button class="btn btn-assign" onclick="assignReservation(${reservation.id}, this)">Assign to Selected Slot</button>
                            <button class="btn btn-reject" onclick="rejectReservation(${reservation.id}, this)">Reject</button>
                        </div>
                    </td>
                </tr>
            `;
        }

        function historyRow(reservation) {
            const user = reservation.user;
            return `
                <tr>
                    <td data-label="Driver">
                        <div><strong>${user?.name || reservation.name}</strong></div>
                        <div style="font-size:12px;color:#6b7280;">${user?.email || '—'}</div>
                    </td>
                    <td data-label="Plate">${format(reservation.plate_number)}</td>
                    <td data-label="Date">${format(reservation.reservation_date)}</td>
                    <td data-label="Time">${format(reservation.reservation_time)}</td>
                    <td data-label="Slot">${formatAssignedSlot(reservation)}</td>
                    <td data-label="Status">${badge(reservation.status)}</td>
                </tr>
            `;
        }

        function formatAssignedSlot(reservation) {
            if (reservation.parking_slot) {
                const areaCode = reservation.parking_slot?.area?.code ?? reservation.parking_no;
                return `${areaCode ?? '—'}-${reservation.parking_slot.slot_number}`;
            }

            if (reservation.parking_no) {
                return reservation.parking_no;
            }

            return '—';
        }

        function renderSlots() {
            if (!slotGrid) {
                return;
            }

            if (!parkingAreas.length) {
                slotGrid.innerHTML = '<div class="table-empty">No parking areas configured.</div>';
                return;
            }

            const html = parkingAreas.map(area => {
                const slotsHtml = area.slots.map(slot => {
                    const selectedClass = slot.id === selectedSlotId ? 'slot-selected' : '';
                    return `<button type="button" class="slot-btn status-${slot.status} ${selectedClass}" data-slot-id="${slot.id}" title="${area.code}-${slot.slot_number}">${slot.slot_number}</button>`;
                }).join('');

                return `
                    <div class="area-card" data-area="${area.code}">
                        <div class="area-header">
                            <h3>${area.name}</h3>
                            <span class="badge-light">${area.code}</span>
                        </div>
                        <div class="slot-list">
                            ${slotsHtml}
                        </div>
                    </div>
                `;
            }).join('');

            slotGrid.innerHTML = html;

            slotGrid.querySelectorAll('.slot-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    selectedSlotId = Number(btn.getAttribute('data-slot-id'));
                    renderSlots();
                    updateSlotDetails();
                });
            });
        }

        function findSlotById(slotId) {
            for (const area of parkingAreas) {
                const slot = area.slots.find(s => s.id === slotId);
                if (slot) {
                    return { ...slot, area };
                }
            }
            return null;
        }

        function updateSlotDetails() {
            if (!slotDetails) {
                return;
            }

            if (!selectedSlotId) {
                slotDetails.innerHTML = 'Select a slot to view its assignment and available actions.';
                return;
            }

            const slotInfo = findSlotById(selectedSlotId);

            if (!slotInfo) {
                slotDetails.innerHTML = 'Selected slot could not be found. Please refresh slots.';
                return;
            }

            const reservation = slotInfo.active_reservation;
            const reservationSummary = reservation
                ? `<p><strong>Reservation (today):</strong> #${reservation.id} — ${reservation.name} (${reservation.plate_number})</p>`
                : '<p><strong>Reservation (today):</strong> None</p>';

            const upcoming = Array.isArray(slotInfo.upcomingReservations)
                ? slotInfo.upcomingReservations
                : (Array.isArray(slotInfo.upcoming_reservations) ? slotInfo.upcoming_reservations : []);
            const upcomingList = upcoming.length
                ? `<div style="margin-top:10px;">
                        <strong>Upcoming reservations for this slot</strong>
                        <ul style="margin:6px 0 0 18px; padding:0;">
                            ${upcoming.map(r => {
                                const d = (r.reservation_date ?? '').toString();
                                const t = (r.reservation_time ?? '').toString();
                                const nm = r.name || (r.user && r.user.name) || '—';
                                const pl = r.plate_number || '—';
                                const isToday = (new Date().toISOString().slice(0,10) === (d || '').slice(0,10));
                                return `<li style="margin:4px 0;${isToday ? 'font-weight:600;' : ''}">#${r.id} — ${nm} (${pl}) — ${d} ${t} <span class="badge-light" style="margin-left:6px;">${r.status}</span></li>`;
                            }).join('')}
                        </ul>
                   </div>`
                : '';

            let actionButtons = '';
            if (slotInfo.status === 'vacant') {
                actionButtons = `
                    <a class="btn btn-assign" href="${walkInUrl}?slot_id=${slotInfo.id}">Open Walk-In Form</a>
                `;
            } else if (slotInfo.status === 'reserved') {
                actionButtons = `
                    <button class="btn btn-approve" onclick="checkInSelectedSlot()">Check In Vehicle</button>
                    <button class="btn btn-reject" onclick="vacateSelectedSlot()">Vacate Slot</button>
                `;
            } else if (slotInfo.status === 'occupied') {
                actionButtons = `
                    <button class="btn btn-reject" onclick="vacateSelectedSlot()">Mark as Vacant</button>
                `;
            }

            slotDetails.innerHTML = `
                <h4>Slot ${slotInfo.area.code}-${slotInfo.slot_number}</h4>
                <p><strong>Status:</strong> ${slotInfo.status.charAt(0).toUpperCase() + slotInfo.status.slice(1)}</p>
                ${reservationSummary}
                ${upcomingList}
                <div class="slot-actions">
                    ${actionButtons || '<span style="color:#6b7280;">No actions available.</span>'}
                </div>
            `;
        }

        async function assignReservation(reservationId, button) {
            if (!selectedSlotId) {
                alert('Select a slot from the map before assigning.');
                return;
            }

            button?.setAttribute('disabled', 'disabled');

            try {
                const response = await fetch(`/admin/api/reservations/${reservationId}/assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ parking_slot_id: selectedSlotId })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Unable to assign reservation.');
                }

                await loadData();
            } catch (error) {
                alert(error.message);
            } finally {
                button?.removeAttribute('disabled');
            }
        }

        async function rejectReservation(reservationId, button) {
            button?.setAttribute('disabled', 'disabled');

            try {
                const response = await fetch(`/admin/api/reservations/${reservationId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Unable to reject reservation.');
                }

                await loadReservations();
            } catch (error) {
                alert(error.message);
            } finally {
                button?.removeAttribute('disabled');
            }
        }

        async function walkInSelectedSlot() {
            if (!selectedSlotId) {
                alert('Select a slot first.');
                return;
            }

            const slotInfo = findSlotById(selectedSlotId);
            if (!slotInfo || slotInfo.status !== 'vacant') {
                alert('Only vacant slots can accept walk-ins.');
                return;
            }

            window.location.href = `${walkInUrl}?slot_id=${slotInfo.id}`;
        }

        async function checkInSelectedSlot() {
            if (!selectedSlotId) {
                alert('Select a slot first.');
                return;
            }

            const slotInfo = findSlotById(selectedSlotId);
            const reservationId = slotInfo?.active_reservation?.id;

            if (!reservationId) {
                alert('No reservation assigned to this slot yet.');
                return;
            }

            try {
                const response = await fetch(`/admin/api/slots/${selectedSlotId}/check-in`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reservation_id: reservationId })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Unable to check in vehicle.');
                }

                await loadData();
            } catch (error) {
                alert(error.message);
            }
        }

        async function vacateSelectedSlot() {
            if (!selectedSlotId) {
                alert('Select a slot first.');
                return;
            }

            try {
                const response = await fetch(`/admin/api/slots/${selectedSlotId}/vacate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Unable to vacate slot.');
                }

                await loadData();
            } catch (error) {
                alert(error.message);
            }
        }
    </script>
</body>
</html>
