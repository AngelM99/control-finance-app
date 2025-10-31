<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.guest')]
#[Title('Iniciar Sesión - Control Finance')]
class Login extends Component
{
    use UsesSpamProtection;

    public HoneypotData $honeypotData;

    public string $email = '';
    public string $password = '';
    public bool $remember = false;

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
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    protected $messages = [
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'Por favor ingrese un correo electrónico válido.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
    ];

    public function login()
    {
        $this->protectAgainstSpam();

        $this->validate();

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials, $this->remember)) {
            request()->session()->regenerate();

            // Check if user is approved
            if (!Auth::user()->isApproved()) {
                Auth::logout();
                session()->flash('error', 'Tu cuenta está pendiente de aprobación por un administrador.');
                return;
            }

            session()->flash('success', '¡Bienvenido de nuevo!');
            return $this->redirect(route('dashboard'), navigate: true);
        }

        $this->addError('email', 'Las credenciales proporcionadas no coinciden con nuestros registros.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
