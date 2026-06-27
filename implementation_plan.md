# Implementation Plan - Asset Filter, Sort, and Request Lab Enhancements

We will add a Category Filter and Date Sorting to the Asset Information table on the Laboratory Show page, combine PC Component names with their specifications in the Request Lab modal for Staff, and provide a tinker command to retrieve/reset the `spvinventory` credentials. Furthermore, we will fix the PC dropdown display inside the Create Transfer and Return Request modals to show formatted PC names (e.g. `PC-00 (Mahasiswa)`) instead of `null`. Lastly, we will add a Template / Prefix Code input field to the Kode Inventaris rows in the Create/Edit Asset modal, make the Total and Good fields read-only for non-PC Components, and dynamically increment/decrement them when Kode Inventaris rows are added or removed. Finally, we will implement dynamic stock count calculation where `Good = Total - Damaged - Loss`.

## User Review Required

> [!IMPORTANT]
> - **Asset Filter & Sort**: The filter and sort features will be executed server-side via request parameters (`category` and `sort`). We will query a separate `$filteredAssets` variable to render the table rows.
> - **Stok Sync Safety**: The `$laboratory->assets` relation will remain fully loaded on the `$laboratory` object so that all assets are still rendered as hidden inputs in the update modal, preventing them from being accidentally deleted during sync.
> - **PC Component Name Formatter**: When selecting "PC Component", the name inside the select element will be shown as `"Asset Name - Specification"`. The read-only "Spesifikasi" input row will be removed from the modal.
> - **PC Dropdown Formatting**: We will map each PC fetched via `/api/labs/{labId}/pcs` to a name corresponding to its index (e.g., `PC-00`, `PC-01`, etc.) so that the dropdown does not display `null (Mahasiswa)`.
> - **Kode Inventaris Template / Prefix**: In the Create/Edit Asset modal, each Kode Inventaris input row will feature an editable "Template / Prefix" input field to the left of the scanned QR Code field. When submitted, they will be joined with a dash (`Prefix-QR`). If loaded in edit mode, existing codes containing dashes will be parsed back into their respective prefix and QR parts.
> - **Stock Total & Good Auto Sync**: Total and Good inputs will be `readonly` for category `PC`, `Electronic`, and `Non-Electronic`. They will auto-increment by 1 when a Kode Inventaris row is added, and auto-decrement by 1 when a row is removed. For `PC Component`, since there are no serial numbers, they remain manually editable.
> - **Damaged / Loss Calculation**: When `Damaged` or `Loss` inputs are edited, `Good` is automatically re-calculated as `Total - Damaged - Loss`. If `Good` falls below 0, the active input (Damaged or Loss) is capped at the maximum allowed value (`Total - other field`), preventing invalid values.

## Proposed Changes

### 1. Laboratory Asset Filter & Sort

#### [MODIFY] [LaboratoryController.php](file:///c:/InventoryFinal2/ICT-Laboratory/app/Http/Controllers/LaboratoryController.php)
- Update the `show(Laboratory $laboratory)` method to retrieve `category` and `sort` (default `desc`) from request.
- Query `$laboratory->assets()` with condition on `assets.asset_category` (if filtered) and sort by pivot table `asset_labs.created_at` (asc/desc).
- Pass `$filteredAssets` to the view.

#### [MODIFY] [show.blade.php](file:///c:/InventoryFinal2/ICT-Laboratory/resources/views/pages/laboratory/show.blade.php)
- Update the tab-restore script block so that it also opens the `asset` section if `request('section') === 'asset'` is present in the URL query string.
- Render the `<x-button.filter>` component in the header of the Asset Information table.
- Populate the filter component with category radios (`All`, `PC Component`, `PC`, `Non Electronic`, `Electronic`) and date sort radios (`Newest to Oldest`, `Oldest to Newest`).
- Include a hidden input for `section=asset` in the filter form so the user stays on the Asset tab after applying.
- Change the loop `@forelse($laboratory->assets as $asset)` to `@forelse($filteredAssets as $asset)`.

#### [MODIFY] [filter.blade.php](file:///c:/InventoryFinal2/ICT-Laboratory/resources/views/components/button/filter.blade.php)
- Exclude `category` and `sort` from the carried-over parameters in the `@foreach(request()->except(...))` loop to prevent duplicate inputs when submitting.

---

### 2. Request Lab PC Component Enhancements

#### [MODIFY] [index.blade.php (Request Lab)](file:///c:/InventoryFinal2/ICT-Laboratory/resources/views/pages/requestlab/index.blade.php)
- Modify the `$assetGroups` mapping in PHP to append the specification to the asset name (`$asset->asset_name . ' - ' . $asset->specification`) if the category is `component-pc`.
- Remove the `js-item-spec-container` div from the initial row in the `#addRequestModal` modal.
- Remove the `js-item-spec-container` div from the template string inside `addItem()`.
- Add safety checks to the javascript functions `updateItemAssetOptions` and `updateItemSpecification` so they check for elements existence (`if (specContainer)`, `if (specInput)`) before reading/mutating them.

---

### 3. PC Dropdown in Transfer & Return Modals

#### [MODIFY] [ReturnRequestController.php](file:///c:/InventoryFinal2/ICT-Laboratory/app/Http/Controllers/ReturnRequestController.php)
- In `getLabPcs(int $labId)`, order the PCs query by `id` so that they follow a consistent sequence.
- Map the retrieved PCs collection, assigning a `pc_name` attribute formatted as `'PC-' . str_pad($index, 2, '0', STR_PAD_LEFT)`.

#### [MODIFY] [index.blade.php (Asset Transfers)](file:///c:/InventoryFinal2/ICT-Laboratory/resources/views/transfer-requests/index.blade.php)
- In the PC dropdown options mapping, use `pc.pc_name || pc.sku || 'PC'` instead of `pc.sku`.

#### [MODIFY] [index.blade.php (Return Requests)](file:///c:/InventoryFinal2/ICT-Laboratory/resources/views/return-requests/index.blade.php)
- In the PC dropdown options mapping, use `pc.pc_name || pc.sku || 'PC'` instead of `pc.sku`.
- Remove `<option value="lost">Lost</option>` from the condition dropdown options.

---

### 4. Kode Inventaris Template Prefix Code & Auto Sync Stock

#### [MODIFY] [modal-asset.blade.php](file:///c:/InventoryFinal2/ICT-Laboratory/resources/views/components/asset/modal-asset.blade.php)
- Update `addSerialInput(list, name, value, locked)` in JavaScript.
  - Parse any existing value: if it has a dash, split it by the last dash into prefix and QR code parts.
  - Create an editable prefix/template text input next to the read-only scanned QR code input.
  - Create a hidden input containing the actual name attribute (`items[x][serials][]` or `serials[]`) which dynamically combines both prefix and scanned QR code fields (joined with a dash) whenever they change.
  - Decrement stock count using `updateStockCount(row, -1)` when a serial row is removed.
- Add Javascript helper functions `getStockInputs(el)` and `updateStockCount(el, delta)`.
- Update `toggleCard(card, cat)` to toggle `readonly` state and background styling of Total and Good inputs.
- Register event click on `.js-add-serial` to also increment stock count using `updateStockCount(e.target, 1)`.
- Set `readonly` attribute in HTML inputs by default for non-PC Component fields.
- Add global `input` event listener to automatically calculate `Good = Total - Damaged - Loss` and apply values capping to prevent negative stock counts.

---

### 5. Tinker Credentials Command

We will provide the following single-line command for `php artisan tinker` to create/reset the SPV Inventory user credentials:
```php
$u = App\Models\User::where('username', 'spvinventory')->first() ?: new App\Models\User; $u->username = 'spvinventory'; $u->role = 'spv inventory'; $u->name = 'SPV Inventory'; $u->nim = '1234567890'; $u->email = 'spv@example.com'; $u->password = Hash::make('123'); $u->save();
```

## Verification Plan

### Automated Tests
- Refresh the pages and check that no javascript/rendering errors are thrown.

### Manual Verification
- Go to `/laboratory/{id}?section=asset`, click **Filter**, choose category "PC Component", click **Apply**. Verify only PC Component items are listed.
- Verify sorting by Date (Newest to Oldest / Oldest to Newest).
- Submit the filter on Category, verify it redirects and stays on the **Asset Information** tab.
- Click "+ Add Asset" in Laboratory detail, select an asset, and verify it updates correctly without losing/deleting other category assets from the laboratory.
- Go to `/requestlab/index` as staff, click **Add Request**.
- Select Category "PC Component", verify the "Asset Name" dropdown lists items formatted as `"Asset Name - Specification"`.
- Verify the "Spesifikasi" row is completely hidden/removed, and changing options does not throw console errors.
- Go to `/transfer-requests` and `/return-requests`, open their create modals. Select origin lab, choose "PC Component" category, and verify the PC dropdown options display computed names like `PC-00 (Mahasiswa)` instead of `null (Mahasiswa)`.
- Open Create Asset modal from Inventory & Stock page. Add a Kode Inventaris row, verify the Template / Prefix field is visible. Enter `L01MJ01` in prefix, type/scan a code `THEYVTE100R`, and verify it saves the combined value `L01MJ01-THEYVTE100R`.
- Click `+ Tambah Kode` in Create Asset modal. Verify `Total` and `Good` automatically increase by 1, and are read-only.
- Click `×` to remove a serial row. Verify `Total` and `Good` automatically decrease by 1.
- Select Category "PC Component", verify `Total` and `Good` inputs are editable.
- In Edit Asset modal, change `Damaged` or `Loss` fields. Verify `Good` dynamically increments/decrements. Capping restricts going below 0.
