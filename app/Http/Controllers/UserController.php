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
    /**
     * Display a listing of the resource.
     */
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
                $query->whereIn('role', $roles);
            })
            ->when(request('status') !== null, function ($query) {
                $query->whereIn('status_user', request('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $laboratories = Laboratory::orderBy('lab_name')->get();

        return view('pages.user.index', compact('users', 'laboratories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('users.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:10', 'unique:users,nim'],
            'role' => ['required', Rule::in(['spv inventory', 'staff'])],
            'username' => ['required', 'string', 'min:8', 'max:25', 'unique:users,username', 'alpha_num:ascii' ],
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
            'status_user' => true,
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

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return redirect()->route('users.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => [
                'required',
                'string',
                'max:10',
                Rule::unique('users', 'nim')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['spv inventory', 'staff'])],
            'username' => [
                'required',
                'string',
                'min:8',
                'max:25',
                'alpha_num:ascii',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
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

    /**
     * Remove the specified resource from storage.
     */
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
        return $labIds;
    }
}

