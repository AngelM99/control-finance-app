<?php

namespace App\Livewire\Lenders;

use App\Models\Lender;
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
            ? 'required|string|max:20|unique:lenders,document_id,' . $this->lender->id
            : 'required|string|max:20|unique:lenders,document_id';

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
