<?php

use App\Models\ListeningParty;
use Livewire\Volt\Component;

new class extends Component {
    public ListeningParty $listeningParty;

    public function mount(ListeningParty $listeningParty): void
    {
        $this->listeningParty = $listeningParty;
    }
}; ?>

<div>
    {{ $listeningParty->name }}
</div>
