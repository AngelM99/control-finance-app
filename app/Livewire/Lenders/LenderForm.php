<?php

namespace App\Livewire\Lenders;

use App\Models\Lender;
use App\Services\DniConsultationService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Prestamista - Control Finance')]
class LenderForm extends Component
{
    public ?Lender $lender = null;

    public $full_name = '';
    public $document_id = '';
    public $phone = '';
    public $email = '';
    public $notes = '';
    public $is_active = true;

    public $searching = false; // Estado de búsqueda

    public function mount($lender = null)
    {
        // Convert to Lender instance if it's an ID
        if ($lender && !($lender instanceof Lender)) {
            $lender = Lender::find($lender);
        }

        if ($lender) {
            // Verificar que el prestamista pertenece al usuario actual
            if ($lender->user_id !== auth()->id()) {
                abort(404);
            }

            $this->lender = $lender;
            $this->full_name = $lender->full_name;
            $this->document_id = $lender->document_id;
            $this->phone = $lender->phone ?? '';
            $this->email = $lender->email ?? '';
            $this->notes = $lender->notes ?? '';
            $this->is_active = $lender->is_active;
        }
    }

    protected function rules()
    {
        $documentRule = $this->lender
            ? ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('lenders', 'document_id')
                ->where('user_id', auth()->id())
                ->ignore($this->lender->id)]
            : ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('lenders', 'document_id')
                ->where('user_id', auth()->id())];

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'document_id' => $documentRule,
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    protected $messages = [
        'full_name.required' => 'El nombre completo es obligatorio.',
        'document_id.required' => 'El DNI o documento de identidad es obligatorio.',
        'document_id.unique' => 'Este documento ya está registrado.',
        'email.email' => 'Por favor ingrese un correo electrónico válido.',
    ];

    /**
     * Busca los datos del DNI en la API de SUNAT
     */
    public function searchDni()
    {
        // Validar que el DNI tenga contenido
        if (empty($this->document_id)) {
            $this->dispatch('show-alert', [
                'type' => 'warning',
                'message' => 'Por favor ingrese un DNI para buscar.'
            ]);
            return;
        }

        // Validar formato del DNI
        $dniService = new DniConsultationService();
        if (!$dniService->isValidDniFormat($this->document_id)) {
            $this->dispatch('show-alert', [
                'type' => 'warning',
                'message' => 'El DNI debe contener exactamente 8 dígitos.'
            ]);
            return;
        }

        // Iniciar búsqueda
        $this->searching = true;

        try {
            $result = $dniService->consultDni($this->document_id);

            if ($result && $result['success']) {
                // Autocompletar el nombre
                $this->full_name = $result['full_name'];

                $this->dispatch('show-alert', [
                    'type' => 'success',
                    'message' => '¡Datos encontrados! Nombre autocompletado.'
                ]);
            } else {
                // No se encontraron datos, permitir ingreso manual
                $this->dispatch('show-alert', [
                    'type' => 'info',
                    'message' => 'No se encontraron datos para este DNI. Puede ingresar el nombre manualmente.'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Error al consultar el DNI. Puede ingresar el nombre manualmente.'
            ]);
        } finally {
            $this->searching = false;
        }
    }

    public function save()
    {
        $validated = $this->validate();
        $validated['user_id'] = auth()->id();

        if ($this->lender) {
            $this->lender->update($validated);
            session()->flash('success', 'Prestamista actualizado exitosamente.');
        } else {
            Lender::create($validated);
            session()->flash('success', 'Prestamista registrado exitosamente.');
        }

        return $this->redirect(route('lenders.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.lenders.lender-form');
    }
}
