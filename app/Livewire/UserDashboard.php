<?php

namespace App\Livewire;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserDashboard extends Component
{
    public $bookings;

    public function mount()
    {
        $user = Auth::user();

        $this->bookings = Booking::with(['transaction', 'passengers', 'accommodations', 'schedule'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('client_email', $user->email);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.user-dashboard');
    }
}
