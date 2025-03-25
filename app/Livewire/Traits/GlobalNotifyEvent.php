<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\On;

trait GlobalNotifyEvent
{
    #[On('notify')]
    public function notify($type, $message)
    {
        session()->flash($type, $message);
    }
}