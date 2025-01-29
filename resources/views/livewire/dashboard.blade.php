<?php

use App\Jobs\ProcessPodcastUrlJob;
use App\Models\Episode;
use App\Models\ListeningParty;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required')]
    public $startTime;

    #[Validate('required|url')]
    public string $mediaUrl = '';

    public function createListeningParty()
    {
        $this->validate();

        $episode = Episode::create([
            'media_url' => $this->mediaUrl,
        ]);

        $listeningParty = ListeningParty::create([
            'episode_id' => $episode->id,
            'name' => $this->name,
            'start_time' => $this->startTime,
        ]);

        ProcessPodcastUrlJob::dispatch($this->mediaUrl, $listeningParty, $episode);

        return redirect()->route('parties.show', $listeningParty);
    }

    public function with()
    {
        return [
            'listeningParties' => ListeningParty::query()
                ->where('is_active', true)
                ->whereNotNull('end_time')
                ->orderBy('start_time', 'asc')
                ->with('episode.podcast')
                ->get(),
        ];
    }
}; ?>

<div class="min-h-screen bg-indigo-50 flex flex-col pt-8">
    <div class="flex justify-center items-center px-4">
        <div class="w-full max-w-2xl">
            <x-card shadow="lg" rounded="lg">
                <h1 class="text-2xl font-mono font-bold text-center">Let's Listen Together</h1>
                <form wire:submit='createListeningParty' class="space-y-6 mt-6">
                    <x-input wire:model='name' placeholder="Listening Party Name"/>
                    <x-input wire:model='mediaUrl' placeholder="Podcast RSS Feed URL"
                             description="Entering the RSS Feed URL will grab the latest episode"/>
                    <x-datetime-picker wire:model='startTime' placeholder="Listening Party Start Time"
                                       :min="now()->subDays(1)"
                                       requires-confirmation/>
                    <x-button type="submit" class="w-full">Create Listening Party</x-button>
                </form>
            </x-card>
        </div>
    </div>
    <div class="my-20 px-4">
        <div class="max-w-2xl mx-auto ">
            <h3 class="text-xl font-mono font-bold mb-8">On going listening parties</h3>
            <div class="bg-white rounded-lg shadow-lg">
                @if($listeningParties->isEmpty())
                    <div class="flex items-center justify-center p-10 text-md font-mono">No listening parties started yet...🥲</div>
                @else
                    @foreach($listeningParties as $listeningParty)
                        <div wire:key="{{  $listeningParty->id }}">
                            <a href="{{ route('parties.show', $listeningParty) }}" class="block">
                                <div
                                    class=" flex items-center justify-between p-4 border-b border-gray-300 hover:bg-gray-50 transition-all duration-150 ease-in-out">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <x-avatar src="{{ $listeningParty->episode->podcast->artwork_url }}"
                                                      size="lg"
                                                      rounded="sm" alt="Podcast artwork"/>
                                        </div>
                                        <div class="flex-1 min-w-0 font-mono">
                                            <p class="text-[0.9rem] font-semibold text-slate-900 truncate">{{ $listeningParty->name }}</p>
                                            <div class="mt-2">
                                                <p class="text-sm truncate text-slate-600 max-w-sm">{{ $listeningParty->episode->title }}</p>
                                                <p class="text-xs uppercase text-slate-500">{{ $listeningParty->podcast->title }}</p>
                                            </div>
                                            <div
                                                class="text-xs text-slate-600 mt-1"
                                                x-data="{
                                                    startTime: '{{ $listeningParty->start_time->timestamp }}',
                                                    countDownText: '',
                                                    isLive: {{ $listeningParty->start_time->isPast() && $listeningParty->is_active ? 'true' : 'false' }},
                                                    updateCountDown() {
                                                        const now = Math.floor(Date.now() / 1000);
                                                        const timeUnitlStart = this.startTime - now;
                                                        if (timeUnitlStart <= 0) {
                                                            this.isLive = true;
                                                        } else {
                                                            const days = Math.floor(timeUnitlStart / 86400);
                                                            const hours = Math.floor((timeUnitlStart % 86400) / 3600);
                                                            const minutes = Math.floor((timeUnitlStart % 3600) / 60);
                                                            const seconds = timeUnitlStart % 60;
                                                            this.countDownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                                                        }
                                                    }
                                                }"
                                                x-init="updateCountDown(); setInterval(() => updateCountDown(), 1000);">

                                                <div x-show="isLive">
                                                    <x-badge flat rose label="Live">
                                                        <x-slot name="prepend" class="relative flex items-center w-2 h-2">
                                                            <span class="absolute inline-flex  w-full h-full rounded-full opacity-75 bg-rose-500 animate-ping"></span>

                                                            <span class="relative inline-flex w-2 h-2 rounded-full bg-rose-500"></span>
                                                        </x-slot>
                                                    </x-badge>
                                                </div>

                                                <div x-show="!isLive">
                                                    Starts in: <span x-text="countDownText"></span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <x-button flat class="w-24 font-bold">Join</x-button>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
