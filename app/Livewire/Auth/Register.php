<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

#[Layout('components.layouts.guest')]
#[Title('Registrarse - Control Finance')]
class Register extends Component
{
    use UsesSpamProtection;

    public HoneypotData $honeypotData;

    public string $name = '';
    public string $email = '';
    public string $dni = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    public function mount()
    {
        // Redirect if already authenticated
        if (Auth::check()) {
            return $this->redirect(route('dashboard'), navigate: true);
        }

        $this->honeypotData = new HoneypotData();
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'dni' => ['required', 'string', 'max:20', 'unique:users,dni'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre completo es obligatorio.',
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'Por favor ingrese un correo electrónico válido.',
        'email.unique' => 'Este correo electrónico ya está registrado.',
        'dni.required' => 'El DNI es obligatorio.',
        'dni.unique' => 'Este DNI ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
        'terms.accepted' => 'Debe aceptar los términos y condiciones.',
    ];

    public function register()
    {
        $this->protectAgainstSpam();

        $validated = $this->validate();

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'dni' => $validated['dni'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_approved' => false, // Requires admin approval
        ]);

        // Assign default role
        $user->assignRole('Usuario Activo');

        session()->flash('success', 'Registro exitoso. Tu cuenta está pendiente de aprobación por un administrador.');

        return $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
