# ⏱️ Enterprise Time Tracker (Full-Stack)

A modern, high-performance Time Entry Dashboard built with a decoupled architecture. This system allows for bulk time entry management, advanced history tracking, and precision record modification in a multi-company environment.

---

## 🏗️ Architecture Overview

The project is split into two specialized applications:

- **[Frontend (Vue.js)](./frontend)**: A premium, single-page dashboard built with Vue 3.5, Vite 8, and Tailwind CSS 4. It focuses on a reactive user experience, dark mode support, and atomic component architecture.
- **[Backend (Laravel)](./backend)**: A robust REST API built with Laravel 13. It handles business logic, complex data relationships (Many-to-Many), and atomic database transactions for bulk operations.

---

## 🚀 Key Features

### 1. Batch Time Entry

- **Bulk Form**: Add multiple rows of time entries and submit them in a single atomic operation.
- **Dynamic Dependencies**: Intelligent dropdowns that filter Employees, Projects, and Tasks based on the selected Company in real-time.
- **Validation**: Comprehensive frontend and backend validation ensuring data integrity and business rule compliance.

### 2. Advanced History & Reporting

- **Live Search**: Debounced search by employee or company name.
- **Smart Filtering**: Filter historical data by company for granular views.
- **Interactive Sorting**: Sort by Date or Hours to analyze work distributions.
- **Optimized Pagination**: Efficient handling of large datasets with clean navigation controls.

### 3. Precision Modification

- **Edit Modal**: A dedicated, glassmorphism-styled modal for updating existing entries without losing page context.
- **State Sync**: The dashboard automatically refreshes and synchronizes state upon successful updates.

### 4. Premium UI/UX

- **Aesthetics**: Minimalist design with a custom `Plus Jakarta Sans` typography.
- **Dark Mode**: Native, persistent dark mode support with smooth transitions.
- **Responsiveness**: Fully optimized for desktops and tablets.

---

## 🛠️ Technical Stack

### Frontend

- **Framework**: Vue 3.5 (Composition API with `<script setup>`)
- **Build Tool**: Vite 8.0
- **Styling**: Tailwind CSS 4.0 (Modern variants and JIT)
- **HTTP Client**: Axios
- **State Management**: Ref/Reactive with Props & Emits architecture

### Backend

- **Framework**: Laravel 13.7
- **Language**: PHP 8.3
- **Database**: MySQL/SQLite
- **API Standards**: JSON API Resources, Form Requests, and Atomic Transactions

---

## ⚙️ Quick Start

### Prerequisites

- Node.js (v20+)
- PHP (v8.3+)
- Composer

### Installation

1.  **Clone the Repository**
2.  **Setup Backend**:
    ```bash
    cd backend
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate --seed
    php artisan serve
    ```
3.  **Setup Frontend**:
    ```bash
    cd frontend
    cp .env.example .env
    npm install
    npm run dev
    ```
4.  **Access**: Open [http://localhost:5173](http://localhost:5173) in your browser.

---

## 📂 Project Assets

### 🚀 API Testing

- **Postman Collection**: (./docs/timesheet_assessment.postman_collection.json)
  - Contains all API endpoints for Companies, Employees, Projects, Tasks, and Time Entries.

### 🤖 AI Conversation History

- **Backend Development Logs**: (./docs/backend_history_chat.json)
- **Frontend Development Logs**: (./docs/frontend_history_chat.json)

### 📸 App Screenshots

#### Dashboard & Bulk Entry

![Dashboard](./docs/Screenshoot%20APP/add_data_entry.png)

#### History View (Light Mode)

![History View](./docs/Screenshoot%20APP/view_history_data.png)

#### History View (Dark Mode)

![History View Dark](./docs/Screenshoot%20APP/view_history_data_dark.png)

#### Edit Entry Modal

![Edit Modal](./docs/Screenshoot%20APP/pop_up_model_for_update.png)

---

## 📄 Documentation

- [Backend API Documentation](./backend/README.md)
- [Frontend Component Walkthrough](./frontend/README.md)
