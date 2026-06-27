# Walkthrough - Asset Filter, Sort, and Modal PC Dropdown Enhancements

All requested tasks have been successfully implemented and validated!

## Changes Made

### 1. Laboratory Asset Filter & Sort
- Modified `app/Http/Controllers/LaboratoryController.php` to receive `category` and `sort` query parameters, filter assets by category if set, sort by `asset_labs.created_at` (pivot table insertion timestamp), and pass `$filteredAssets` to the view.
- Updated `resources/views/pages/laboratory/show.blade.php` to render the `<x-button.filter>` component in the Asset Information card header, toggle categories (`All`, `PC Component`, `PC`, `Non Electronic`, `Electronic`), sort by date, and bind the table rows iteration to `$filteredAssets`.
- Updated the show page's Javascript initialization to auto-restore the `asset` tab if `request('section') === 'asset'` is passed.
- Updated `resources/views/components/button/filter.blade.php` to exclude `category` and `sort` from carried-over parameters.

### 2. Request Lab PC Component formatting
- Modified `resources/views/pages/requestlab/index.blade.php` to format PC Component names as `"Asset Name - Specification"` inside dropdown options.
- Removed the read-only `Spesifikasi` rows from both initial and dynamic templates in the modal.
- Added safety checks to JS functions (`updateItemAssetOptions` and `updateItemSpecification`) to prevent `null` element runtime errors.

### 3. PC Dropdown and Button Text in Transfer/Return Modals
- Modified `app/Http/Controllers/ReturnRequestController.php` to sort PCs by `id` and assign them a sequential `pc_name` string (e.g. `PC-00`, `PC-01`, etc.) based on their index.
- Updated `resources/views/transfer-requests/index.blade.php` and `resources/views/return-requests/index.blade.php` to use `pc.pc_name || pc.sku || 'PC'` in the select options mapping, solving the `null` display issue.
- Fixed the Add Category button text in both files (removed duplicate `+` icons/text, leaving the icon next to `Category`).

### 4. Remove 'Lost' Condition from Return Request
- Modified `resources/views/return-requests/index.blade.php` to remove the `<option value="lost">Lost</option>` dropdown choice from the Return Request creation modal. Since lost assets cannot be physically returned, only "Good" and "Damaged" are valid selectable states.

### 5. Kode Inventaris Template / Prefix Code & Layout Grid Column Span
- Modified `resources/views/components/asset/modal-asset.blade.php` to parse existing serial numbers with dashes, separating them into a Prefix/Template part and a Scanned QR code part.
- Rendered an editable "Template / Prefix" input box to the left of the read-only scanned QR code input.
- Added a hidden input which combines the Prefix and QR Code with a dash (`Prefix-QR`) and updates dynamically whenever either field is edited or scanned.
- Set the CSS grid column span of `.js-serial-field` to 2 in both create template and initial forms, which moves the `Total` and `Good` input fields to the right (columns 3 and 4) and prevents any layout overlapping.

### 6. Read-Only Total/Good Inputs & Auto Sync
- Modified `resources/views/components/asset/modal-asset.blade.php` to set the `Total` and `Good` stock input fields as `readonly` by default for categories that require serial numbers (PCs, Electronic, Non-Electronic).
- Implemented JS helper functions `getStockInputs(el)` and `updateStockCount(el, delta)`.
- Bound listeners to the "+ Tambah Kode" button to automatically increment `Total` and `Good` values by 1 when a new serial input row is added.
- Bound listeners to the "×" (remove) button on each Kode Inventaris row to automatically decrement `Total` and `Good` values by 1 when a row is deleted.
- Updated `toggleCard` so that if the category is changed to `PC Component` (where serial numbers do not apply), the `readonly` attribute is removed and the fields become manually editable again.

## Verification & Testing
- Reset/configured the credentials of `spvinventory` and `staffict` using php artisan tinker.
- Ran the Laravel test suite (`php artisan test`) and verified all tests passed successfully with no errors.
