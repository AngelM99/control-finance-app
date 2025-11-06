<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Lender;
use App\Models\Transaction;
use App\Models\FinancialProduct;
use App\Services\OtpService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

#[Layout('components.layouts.guest')]
#[Title('Consulta Pública - Control Finance')]
class PublicConsultation extends Component
{
    use UsesSpamProtection;

    public HoneypotData $honeypotData;

    public string $dni = '';
    public string $consultationType = 'lender'; // lender o owner
    public string $otp = '';
    public string $step = 'dni'; // dni, otp, results
    public ?User $user = null;
    public ?Lender $lender = null;
    public $financialProducts = [];
    public $transactions = [];

    public function mount()
    {
        $this->honeypotData = new HoneypotData();
    }

    protected function rules()
    {
        if ($this->step === 'dni') {
            return [
                'dni' => ['required', 'string', 'exists:users,dni'],
            ];
        }

        if ($this->step === 'otp') {
            return [
                'otp' => ['required', 'string', 'size:6'],
            ];
        }

        return [];
    }

    protected $messages = [
        'dni.required' => 'El DNI es obligatorio.',
        'dni.exists' => 'No se encontró ningún usuario con este DNI.',
        'otp.required' => 'El código OTP es obligatorio.',
        'otp.size' => 'El código OTP debe tener 6 dígitos.',
    ];

    public function requestOtp()
    {
        $this->protectAgainstSpam();

        $this->validate();

        $otpService = app(OtpService::class);
        $otpToken = $otpService->generateOtp($this->dni);

        if (!$otpToken) {
            $this->addError('dni', 'No se pudo generar el código OTP. Por favor intente más tarde.');
            return;
        }

        // In production, send OTP via SMS or email
        // For now, we'll just flash it to the session for testing
        session()->flash('otp_generated', "Código OTP generado: {$otpToken->token} (válido por 10 minutos)");

        $this->step = 'otp';
    }

    public function verifyOtp()
    {
        $this->protectAgainstSpam();

        $this->validate();

        $otpService = app(OtpService::class);

        if (!$otpService->validateOtp($this->dni, $this->otp)) {
            $this->addError('otp', 'El código OTP es inválido o ha expirado.');
            return;
        }

        // Load user data
        $this->user = User::where('dni', $this->dni)->first();

        // Load financial products
        $this->financialProducts = FinancialProduct::where('user_id', $this->user->id)
            ->with(['transactions' => function($query) {
                $query->latest()->limit(5);
            }])
            ->get();

        $this->step = 'results';
    }

    public function consultLender()
    {
        $this->protectAgainstSpam();

        $this->validate([
            'dni' => ['required', 'string'],
        ]);

        // Buscar prestamista por DNI
        $this->lender = Lender::where('document_id', $this->dni)
            ->where('is_active', true)
            ->first();

        if (!$this->lender) {
            $this->addError('dni', 'No se encontró ningún prestamista con este DNI o está inactivo.');
            return;
        }

        // Cargar transacciones del prestamista (solo con productos válidos)
        $this->transactions = Transaction::where('lender_id', $this->lender->id)
            ->whereHas('financialProduct') // Filtrar transacciones huérfanas
            ->with(['financialProduct', 'financialProduct.user'])
            ->latest('transaction_date')
            ->get();

        $this->step = 'results';
    }

    public function resetForm()
    {
        $this->dni = '';
        $this->otp = '';
        $this->consultationType = 'lender';
        $this->step = 'dni';
        $this->user = null;
        $this->lender = null;
        $this->financialProducts = [];
        $this->transactions = [];
    }

    public function render()
    {
        return view('livewire.public-consultation');
    }
}
