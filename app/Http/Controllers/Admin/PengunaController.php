<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PengunaController extends Controller
{
    public function index()
    {
        $users = User::query()->latest('id')->get()->map(fn (User $user) => $this->present($user))->values();

        if (request()->expectsJson()) {
            return response()->json($users);
        }

        return view('admin.users', ['users' => $users]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = User::create($data);

        return response()->json($this->present($user), 201);
    }

    public function update(Request $request, User $user)
    {
        if ($user->is(Auth::user()) && $request->input('status') !== $user->status) {
            abort(422, 'Status akun yang sedang digunakan tidak dapat diubah.');
        }

        $data = $this->validated($request, $user);
        $user->update($data);

        return response()->json($this->present($user->refresh()));
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate(['password' => ['required', 'string', 'min:6', 'confirmed']]);
        $user->update(['password' => $data['password']]);

        return response()->json(['message' => 'Password berhasil direset.']);
    }

    public function destroy(User $user)
    {
        abort_if($user->is(Auth::user()), 422, 'Akun yang sedang digunakan tidak dapat dihapus.');
        abort_if($user->role === 'admin' && User::where('role', 'admin')->where('status', 'active')->count() <= 1, 422, 'Minimal harus ada satu admin aktif.');

        $user->delete();

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['nullable', 'email', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'petugas'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ]);

        if ($user && blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    private function present(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role === 'admin' ? 'Admin' : 'Operator',
            'roleValue' => $user->role,
            'status' => $user->status === 'active' ? 'Aktif' : 'Tidak Aktif',
            'statusValue' => $user->status,
            'lastLogin' => optional($user->last_login_at)->format('d M Y, H:i') ?? '-',
            'color' => $user->role === 'admin' ? 'blue' : 'green',
        ];
    }
}
