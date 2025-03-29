<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de registro para clientes
     */
    public function showClientRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Procesar registro de cliente
     */
    public function registerClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'names' => 'required|string|max:255',
            'last_names' => 'required|string|max:255',
            'phone_number' => 'nullable|integer',
            'dui' => 'required|integer|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Obtener rol de cliente
        $clientRole = Role::where('name', 'client')->first();

        $user = User::create([
            'names' => $request->names,
            'last_names' => $request->last_names,
            'phone_number' => $request->phone_number,
            'dui' => $request->dui,
            'email' => $request->email,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role_id' => $clientRole->id,
            'status' => 'pending',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    /**
     * Mostrar formulario de registro para empleados (por admin de empresa)
     */
    public function showEmployeeRegisterForm()
    {
        // Verificar que el usuario actual es un admin de empresa
        if (!Auth::user()->hasRole('company_admin')) {
            abort(403);
        }

        return view('auth.register_employee');
    }

    /**
     * Procesar registro de empleado
     */
    public function registerEmployee(Request $request)
    {
        // Verificar que el usuario actual es un admin de empresa
        if (!Auth::user()->hasRole('company_admin')) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'names' => 'required|string|max:255',
            'last_names' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Obtener rol de empleado
        $employeeRole = Role::where('name', 'employee')->first();

        // Generar contraseña aleatoria
        $password = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);

        $user = User::create([
            'names' => $request->names,
            'last_names' => $request->last_names,
            'email' => $request->email,
            'dui' => $request->dui ?? 0, // Se puede ajustar según necesidad
            'password' => Hash::make($password),
            'role_id' => $employeeRole->id,
            'company_id' => Auth::user()->company_id,
            'status' => 'active',
        ]);

        // Aquí se debería enviar un correo al empleado con su contraseña

        return redirect()->route('company.employees.index')
            ->with('success', 'Empleado registrado correctamente. Contraseña: ' . $password);
    }

    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Verificar si la cuenta está activa
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está pendiente de verificación o ha sido desactivada.',
                ]);
            }

            // Redireccionar según el rol
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('company_admin')) {
                return redirect()->route('company.dashboard');
            } elseif ($user->hasRole('employee')) {
                return redirect()->route('employee.redeem');
            } elseif ($user->hasRole('client')) {
                return redirect()->route('client.dashboard');
            }

            return redirect()->route('home');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->withInput($request->except('password'));
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}