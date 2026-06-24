# Project Context: ICT Inventory System

## 1. Project Overview
**Purpose:** 
The ICT Inventory System is a comprehensive web-based application designed to manage, track, and distribute IT assets, PC components, and laboratory equipment. It provides end-to-end lifecycle management for assets, from procurement (Request) and placement (Transfer/Lab) to maintenance (Return) and tracking (QR Code Scanning).

**Main Business Flow:**
1. **Asset Entry:** Assets are registered in the system (Electronic, Non-Electronic, PC, Component PC).
2. **Serial Number Generation:** Electronic and PC components get unique Serial Numbers (and automatically generated QR Codes).
3. **Distribution & Mapping:** Assets can be assigned to specific Laboratories or mapped inside a specific PC (e.g., RAM slotted into PC-01).
4. **Logistics:** Staff can request assets, transfer them between labs, or return damaged/unused assets.
5. **Tracking:** Users can scan an asset's QR Code using their device's camera or by uploading an image to instantly view its status, condition, and location.

**Technology Stack:**
- **Backend:** Laravel 12, PHP 8.2
- **Database:** MySQL / SQLite
- **Frontend:** Blade Templates, TailwindCSS, Livewire
- **Admin Panel:** Filament PHP (v3)
- **Utilities:** `endroid/qr-code` (Backend QR generation), `html5-qrcode` & `jsQR` (Frontend QR Scanning), `barryvdh/laravel-dompdf` (PDF Export).

---

## 2. Current Features
* **Dashboard (`DashboardController`):** Provides a high-level overview of total assets, conditions (Good, Damaged, Lost), and recent activity logs.
* **Asset Management (`AssetController`, `SerialNumberController`):** 
  - CRUD for assets with auto-generated SKUs.
  - Generates detailed Serial Numbers for tracked categories.
* **Scan Code (`ScanCodeController`, `QrCodeController`):** 
  - Instantly reads barcodes/QR codes via Camera or Image Upload.
  - Displays asset/serial details if matched.
  - Uses `QrCodeController` to serve PNG QR Codes server-side (`/qr?text=...`).
* **Laboratory & PC Management (`LaboratoryController`, `PcController`):**
  - Manage lab rooms and map PC entities to labs.
  - PC Components mapping (linking Serial Numbers to PC slots).
* **Requests & Transfers (`RequestLabController`, `TransferRequestController`, `ReturnRequestController`):**
  - Full workflow for moving assets. Includes approval and rejection modals.
* **Activity & Asset Logging (`ActivityLogController`, `AssetLogController`):** 
  - Comprehensive audit trails for every asset movement or condition change.

---

## 3. Database Structure
**Core Tables:**
- `users`: Manages authentication (`role` includes Admin, PIC, etc.).
- `laboratories`: Lab spaces (`lab_name`, `location`).
- `assets`: Master item data (`sku`, `asset_name`, `asset_category`, `total_asset`).
- `asset_serial_numbers`: Individual unit tracking (`asset_id`, `serial_number`, `condition`, `status`, `slot`).
- `pcs`: Workstations linked to a lab (`lab_id`, `pc_name`).

**Transaction Tables:**
- `request_labs` & `request_items`: Procurement/Asset requests.
- `transfer_requests` & `transfer_request_items`: Moving assets between labs.
- `return_requests` & `return_request_items`: Returning items to the main inventory.
- `asset_logs` & `activity_logs`: Immutable history tracking.

**Key Relationships:**
- `Asset` HasMany `AssetSerialNumber`.
- `Pc` BelongsTo `Laboratory`.
- `AssetSerialNumber` BelongsTo `Laboratory` and BelongsTo `Pc` (via mapping).

---

## 4. Authentication & Authorization
- **Middleware:** The application uses Laravel's built-in `auth` middleware to protect all internal dashboard routes.
- **Roles:** Handled primarily via a `role` column in the `users` table (e.g., `admin`, `pic`).
- **Public Routes:** Authentication routes (`/login`) and the QR code generation endpoint (`/qr`) are public. `/qr` is public so that `<img>` tags in PDFs or Modals can fetch the image without cross-origin or token issues.

---

## 5. Recent Changes (Current Session)
- **Refactored QR Code Scanning Logic (`scan-code/index.blade.php`):**
  - **Replaced Buggy Upload Scanner:** Swapped `html5-qrcode`'s file upload scanner with **`jsQR`**. `Html5Qrcode` struggled to read server-generated PNGs due to hidden canvas rendering bugs. `jsQR` reads directly from off-screen `ImageData`, providing instantaneous and 100% reliable image decoding.
  - **Terminology Update:** Renamed all instances of "Barcode" to "QR Code" across the scanning UI to match user intent.
  - **Custom Error Messages:** Updated the backend and frontend to explicitly show *"QR Code tidak sesuai"* when a scanned valid QR is not found in the DB.
- **Moved QR Generation to Backend (`QrCodeController.php`):**
  - Eliminated frontend JS rendering issues for QR codes by generating them purely server-side using `endroid/qr-code`.
  - Removed old CDN scripts (`qrcode.js`) from the asset index view.

---

## 6. Pending Tasks & Suggested Next Steps
- **Pending Features:** 
  - Advanced PC Component mapping UI (drag-and-drop slots).
  - Bulk printing of QR Codes (PDF generation for multiple Serial Numbers at once).
- **Known Quirks:** 
  - The live camera scanner (`html5-qrcode`) requires HTTPS or `localhost` to access the device camera due to browser security policies.
- **Technical Debt:** 
  - Some custom Blade views (`pages/`) might eventually be migrated to native Filament Resources if the team decides to standardize the admin panel entirely on Filament.

---

## 7. Project Architecture
- **`app/Models/`**: Eloquent models with strictly defined relationships and `$fillable` properties.
- **`app/Http/Controllers/`**: Traditional MVC controllers handling business logic for the custom Blade views.
- **`resources/views/pages/`**: Contains the customized, modern UI (TailwindCSS) dashboard interfaces, separate from standard Filament pages.
- **`resources/views/panel/`**: Contains the layout wrappers (`sidebar`, `navbar`, `content`).
- **`routes/web.php`**: Route definitions clearly grouped by authentication middleware.

---

## 8. Setup Instructions
To set up this project locally:
1. **Clone & Install:**
   ```bash
   composer install
   npm install
   ```
2. **Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. **Database:**
   Ensure MySQL/SQLite is running and configured in `.env`.
   ```bash
   php artisan migrate --seed
   ```
4. **Run Application:**
   ```bash
   php artisan serve
   npm run dev
   ```

---

## 9. Developer Notes
- **QR Code Images in DB:** **DO NOT** attempt to compress and save generated QR Code images (PNGs) into the database. QR codes are just textual representations. We only store the `serial_number` string in the DB. The PNGs are generated on-the-fly via `/qr?text=[SERIAL]` which is highly performant and keeps the database perfectly lightweight.
- **Image Scanning (jsQR):** If you need to modify the QR upload feature, note that we draw the uploaded image to an offscreen `<canvas>` and pass the `ImageData` to `jsQR`. Do not use `display: none` on canvas wrappers during this process, as browsers (especially WebKit) will drop the pixel data to save memory, breaking the scanner. Use `position: absolute; top: -9999px; visibility: hidden;` instead.
- **Styling:** The project utilizes vanilla CSS variables alongside Tailwind to enforce a specific premium aesthetic (glassmorphism, subtle gradients, `var(--bg-card)` tokens). Always respect these design tokens when adding new components.
