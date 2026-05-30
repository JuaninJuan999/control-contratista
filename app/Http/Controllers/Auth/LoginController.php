<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserUsabilidadTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private readonly UserUsabilidadTracker $usabilidadTracker
    ) {}

    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->whereRaw('LOWER(username) = ?', [mb_strtolower($validated['username'], 'UTF-8')])
            ->first();

        if ($user === null || ! $user->activo || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $this->usabilidadTracker->iniciarSesion($user);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        if ($request->user() !== null) {
            $this->usabilidadTracker->cerrarSesionActual($request->user());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
