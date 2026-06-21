<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Exports\UserExport;
use Throwable;

class UserController extends Controller
{
    /** Role yang valid di seluruh aplikasi. */
    private const ROLES = ['spv inventory', 'pic', 'staff'];

    public function index()
    {
        $users = User::with('labs')
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->when(request('role'), function ($query, $roles) {
                $query->whereIn('role', (array) $roles);
            })
            ->when(request('status') !== null, function ($query) {
                $query->whereIn('status_user', (array) request('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $laboratories = Laboratory::orderBy('lab_name')->get();

        return view('pages.user.index', compact('users', 'laboratories'));
    }

    public function create()
    {
        return redirect()->route('users.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:10', 'unique:users,nim'],
            'role' => ['required', Rule::in(self::ROLES)],
            'username' => ['required', 'string', 'min:8', 'max:25', 'unique:users,username', 'alpha_num:ascii'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'status_user' => ['nullable', 'boolean'],

            'lab_ids' => ['nullable', 'array'],
            'lab_ids.*' => ['exists:laboratories,id'],
        ]);

        $labIds = $this->validatedLabIdsForRole($validated['role'], $validated['lab_ids'] ?? []);

        $user = User::create([
            'name' => $validated['name'],
            'nim' => $validated['nim'],
            'role' => $validated['role'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status_user' => $request->boolean('status_user', true),
        ]);

        $user->labs()->sync($labIds);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => 'Created user: ' . $user->name,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return redirect()->route('users.index');
    }

    public function edit(User $user)
    {
        return redirect()->route('users.index');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => [
                'required', 'string', 'max:10',
                Rule::unique('users', 'nim')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(self::ROLES)],
            'username' => [
                'required', 'string', 'min:8', 'max:25', 'alpha_num:ascii',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'status_user' => ['nullable', 'boolean'],

            'lab_ids' => ['nullable', 'array'],
            'lab_ids.*' => ['exists:laboratories,id'],
        ]);

        $labIds = $this->validatedLabIdsForRole($validated['role'], $validated['lab_ids'] ?? []);

        $data = [
            'name' => $validated['name'],
            'nim' => $validated['nim'],
            'role' => $validated['role'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'status_user' => $request->boolean('status_user'),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->labs()->sync($labIds);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => 'Updated user: ' . $user->name,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity' => 'Deleted user: ' . $user->name,
            ]);

            return redirect()
                ->route('users.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (Throwable $e) {
            return redirect()
                ->route('users.index')
                ->with('error', 'User gagal dihapus.');
        }
    }

    public function export(string $format)
    {
        $export = new UserExport();

        return match ($format) {
            'pdf' => $export->downloadPdf(),
            'excel' => $export->downloadExcel(),
            'csv' => $export->downloadCsv(),
            default => abort(404),
        };
    }

    /**
     * Aturan jumlah laboratory per role:
     *  - spv inventory : tidak terikat lab (boleh kosong).
     *  - staff         : WAJIB TEPAT 1 lab.
     *  - pic           : WAJIB minimal 1 lab, boleh lebih.
     */
    private function validatedLabIdsForRole(string $role, array $labIds): array
    {
        $labIds = array_values(array_unique(array_filter($labIds)));

        if ($role === 'spv inventory') {
            return [];
        }

        if (empty($labIds)) {
            throw ValidationException::withMessages([
                'lab_ids' => 'Pilih minimal satu laboratory untuk role ini.',
            ]);
        }

        if ($role === 'staff' && count($labIds) > 1) {
            throw ValidationException::withMessages([
                'lab_ids' => 'Staff hanya boleh memiliki tepat 1 laboratory.',
            ]);
        }

        return $labIds;
    }
}