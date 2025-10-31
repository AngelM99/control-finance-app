<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Usuarios Pendientes - Control Finance')]
class PendingUsers extends Component
{
    use WithPagination;

    public function approve($userId)
    {
        $user = User::findOrFail($userId);

        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        session()->flash('success', "Usuario {$user->name} aprobado exitosamente.");
    }

    public function reject($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();

        session()->flash('success', 'Usuario rechazado y eliminado.');
    }

    public function render()
    {
        $pendingUsers = User::where('is_approved', false)
            ->latest()
            ->paginate(10);

        $totalUsers = User::count();
        $activeUsers = User::where('is_approved', true)->count();

        return view('livewire.admin.pending-users', [
            'pendingUsers' => $pendingUsers,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
        ]);
    }
}
