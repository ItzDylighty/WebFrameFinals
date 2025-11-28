# EseePark - Parking Reservation System
## Project Setup Guide

---

## 📌 Project Description

**EseePark** is a Laravel-based parking reservation system designed to solve real-world parking challenges. It allows users to:
- Reserve parking spaces from different parking locations
- Manage their reservations (create, read, update, delete)
- View available parking spaces
- Track their parking history

---

## 🛠️ Technology Stack

- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **Database**: MySQL (to be implemented)
- **Frontend**: Blade Templates (to be implemented)
- **API**: RESTful JSON API

---

## 📋 Current Baseline Structure

### Models
- **Reservation** (`app/Models/Reservation.php`)
  - Represents a parking reservation
  - Fields: name, plate_number, reservation_date, reservation_time, parking_no, phone_no

### Controllers
- **ReservationController** (`app/Http/Controllers/ReservationController.php`)
  - Handles all CRUD operations for reservations
  - Methods: index, store, show, update, destroy

### Routes
- **API Routes** (`routes/web.php`)
  - GET `/api/reservations` - List all reservations
  - POST `/api/reservations` - Create new reservation
  - GET `/api/reservations/{id}` - Get single reservation
  - PUT `/api/reservations/{id}` - Update reservation
  - DELETE `/api/reservations/{id}` - Delete reservation

### Database
- **Migration Template** (`database/migrations/2025_11_28_000000_create_reservations_table.php`)
  - Template for creating reservations table
  - To be implemented by development team

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 5.7 or higher
- Git

### Installation Steps

1. **Navigate to project directory**:
   ```bash
   cd EseePark
   ```

2. **Install dependencies** (already done):
   ```bash
   composer install
   ```

3. **Environment Setup** (already done):
   - `.env` file is already configured
   - Application key is already generated

4. **Run the development server**:
   ```bash
   php artisan serve
   ```
   - Application will be available at: `http://127.0.0.1:8000`

---

## 📝 Next Steps for Development Team

### Phase 1: Database Implementation
1. Implement the migration in `database/migrations/2025_11_28_000000_create_reservations_table.php`
2. Run migrations: `php artisan migrate`
3. Verify database tables are created

### Phase 2: User Interface
1. Create Blade templates in `resources/views/reservations/`
2. Implement forms for creating and editing reservations
3. Create list and detail views
4. Add styling with Bootstrap or Tailwind CSS

### Phase 3: Validation & Security
1. Enhance form validation rules
2. Add authorization checks
3. Implement user authentication

### Phase 4: Additional Features
1. Parking space management
2. Payment integration
3. Notifications system
4. Reporting and analytics

---

## 🧪 Testing the API

### Using cURL

**Get all reservations**:
```bash
curl http://127.0.0.1:8000/api/reservations
```

**Create a reservation**:
```bash
curl -X POST http://127.0.0.1:8000/api/reservations \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "plate_number": "ABC-1234",
    "reservation_date": "2025-12-01",
    "reservation_time": "14:30",
    "parking_no": "A-101",
    "phone_no": "0123456789"
  }'
```

**Get single reservation**:
```bash
curl http://127.0.0.1:8000/api/reservations/1
```

**Update reservation**:
```bash
curl -X PUT http://127.0.0.1:8000/api/reservations/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "phone_no": "0987654321"
  }'
```

**Delete reservation**:
```bash
curl -X DELETE http://127.0.0.1:8000/api/reservations/1
```

### Using Postman
1. Import the API endpoints into Postman
2. Test each endpoint with appropriate request bodies
3. Verify responses and status codes

---

## 📂 Project Structure

```
EseePark/
├── app/
│   ├── Models/
│   │   └── Reservation.php
│   └── Http/
│       └── Controllers/
│           └── ReservationController.php
├── database/
│   └── migrations/
│       └── 2025_11_28_000000_create_reservations_table.php
├── routes/
│   └── web.php
├── resources/
│   └── views/
│       └── (to be created)
├── IMPROVEMENTS.md (Development guide)
├── PROJECT_SETUP.md (This file)
└── ... (other Laravel files)
```

---

## 🔧 Useful Artisan Commands

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Create a new model
php artisan make:model ModelName

# Create a new controller
php artisan make:controller ControllerName

# Create a new migration
php artisan make:migration migration_name

# Run tests
php artisan test

# Clear cache
php artisan cache:clear

# View all routes
php artisan route:list
```

---

## 📚 Documentation

- **IMPROVEMENTS.md** - Detailed guide for implementing database, UI, and additional features
- **Laravel Documentation** - https://laravel.com/docs

---

## ✅ Baseline Checklist

- [x] Laravel project created
- [x] Reservation model created
- [x] ReservationController with CRUD operations created
- [x] API routes configured
- [x] Migration template created
- [x] IMPROVEMENTS.md guide created
- [ ] Database migration implemented (by development team)
- [ ] UI/Views implemented (by development team)
- [ ] User authentication added (by development team)
- [ ] Additional features implemented (by development team)

---

## 📞 Support

For questions about the baseline structure, refer to:
1. IMPROVEMENTS.md - Implementation guide
2. Laravel Documentation - https://laravel.com/docs
3. Code comments in controller and model files

---

**Project Created**: 2025-11-28
**Status**: Baseline Ready for Development
