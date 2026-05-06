# Time Tracker Frontend (Vue 3 + Vite)

A premium, high-fidelity dashboard for managing time entries, built with **Vue 3.5**, **Vite 8**, and **Tailwind CSS 4**.

---

## 🎨 Design Philosophy
*   **Minimalist & Soft**: Uses a Slate and Indigo color palette for a professional yet approachable feel.
*   **Premium Typography**: Integrated `Plus Jakarta Sans` for modern readability.
*   **Interactive Feedback**: Subtle hover effects, smooth transitions, and glassmorphism-styled modals.
*   **Native Dark Mode**: Full theme support with persistence in `localStorage`.

---

## 🏗️ Component Architecture
The application has been refactored into modular, single-responsibility components:

### 1. `TimeEntryForm.vue`
*   Handles the **Batch Entry** system.
*   Manages dynamic rows and row-specific dependencies (fetching Employees/Projects/Tasks per company).
*   Triggers atomic bulk submissions.

### 2. `TimeEntryHistory.vue`
*   Manages the interactive **History Table**.
*   Includes built-in state for **Searching**, **Filtering**, **Sorting**, and **Pagination**.
*   Exposes a `refresh` method for parent-child synchronization.

### 3. `EditEntryModal.vue`
*   An encapsulated modal for **Precision Updates**.
*   Handles its own dependency fetching and validation states to ensure the main page remains lightweight.

---

## 🚀 Technical Highlights
*   **Reactivity System**: Leverages Vue 3.5's high-performance reactivity for real-time form feedback.
*   **Atomic Communication**: Uses a robust Props & Emits pattern for clean data flow between components.
*   **Tailwind 4.0**: Utilizes the latest CSS-first engine for optimized styling and modern variants (e.g., `group-hover/th`).
*   **Debounced Search**: Implements efficient watchers for search filtering to minimize API overhead.

---

## 🛠️ Development Setup

1. **Setup environment**:
   ```bash
   cp .env.example .env
   ```
2. **Install dependencies**:
   ```bash
   npm install
   ```
3. **Start the dev server**:
   ```bash
   npm run dev
   ```
4. **Build for production**:
   ```bash
   npm run build
   ```

---

## 📦 Dependencies
- `vue`: ^3.5.32
- `tailwindcss`: ^4.2.4
- `axios`: ^1.16.0
- `vite`: ^8.0.10
