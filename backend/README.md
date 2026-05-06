# Time Entry System - Backend API

A professional, enterprise-ready Time Entry Management System built with **Laravel 13**, designed for high-performance data entry and robust referential integrity in multi-tenant (multi-company) environments.

## 🛠 Technical Stack

- **Framework**: [Laravel 13.x](https://laravel.com)
- **Runtime**: PHP 8.3+
- **Database**: SQLite (Development) / MySQL & PostgreSQL compatible
- **Architecture**:
    - **Service Layer**: Controller-Request-Model pattern.
    - **Validation**: Dedicated Form Requests with custom business rules.
    - **Transformation**: API Resources for consistent JSON serialization.
    - **Concurrency**: Database Transactions for atomic bulk operations.

## 🚀 Core Features

### 1. High-Performance Bulk Storage

- **Atomic Batches**: Process multiple time entries in a single `POST` request.
- **Rollback Safety**: Uses `DB::transaction` to ensure that if one entry fails validation, the entire batch is rolled back, preventing partial or corrupt data states.

### 2. Intelligent Business Logic

- **Hierarchical Validation**: Ensures Tasks belong to the specified Company during both creation and updates.

### 3. Advanced Data Listing

- **Global Search**: Search records across the entire history by Employee or Company name.
- **Dynamic Sorting**: Frontend-driven sorting on any relevant column (`date`, `hours`, `id`, `created_at`).
- **Smart Pagination**: Optimized metadata return (`current_page`, `last_page`, `total`, `per_page`) without redundant URL strings.
- **Optimized Loading**: Uses Eloquent Eager Loading (`with(['company', 'employee', ...])`) to eliminate the N+1 query problem.

### 4. Precision Record Updates

- **Secure PUT Endpoint**: Dedicated route for individual record modification.
- **Live Hydration**: Returns the fully updated object with all relationships pre-loaded, enabling seamless UI updates in the frontend.

### 5. Standardized API Response

Every endpoint adheres to a strict JSON contract for predictable frontend consumption:

```json
{
    "success": boolean,
    "message": string,
    "data": mixed | null,
    "errors": mixed | null
}
```

---

## 🏗 Database Schema & Relations

### Entity Relationships

- **Company**: The root tenant. Has many Projects and Tasks.
- **Employee**: Can belong to multiple Companies (**Many-to-Many** via `company_employee` pivot).
- **Project**: Belongs to a Company.
- **Task**: Belongs to a Company.
- **TimeEntry**: The central fact table referencing all entities above.

---

## 🛠 API Documentation

### Initial Data

`GET /api/init-data?company_id={id}`

- Fetches optimized dropdown data (ID/Name) for all form-related entities.
- Supports company-based filtering for dependent dropdowns.

### Time Entry Management

- **List (History)**: `GET /api/time-entries`
    - Params: `company_id`, `search`, `sort_by`, `sort_order`, `per_page`, `page`.
- **Bulk Store**: `POST /api/time-entries`
    - Payload: `{ "entries": [ { ... }, { ... } ] }`
- **Update**: `PUT /api/time-entries/{id}`
    - Payload: `{ "company_id", "employee_id", "project_id", "task_id", "date", "hours" }`

---

## ⚙️ Setup Instructions

1. **Clone & Install**:
    ```bash
    composer install
    ```
2. **Environment Setup**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
3. **Database Initialization**:

    ```bash
    php artisan migrate:fresh --seed
    ```

    _Note: The seeder creates a realistic testing environment with edge-case conflict scenarios pre-populated._

4. **Start Server**:
    ```bash
    php artisan serve
    ```
