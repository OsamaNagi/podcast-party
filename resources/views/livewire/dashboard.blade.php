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
                ->orderBy('start_time', 'asc')
                ->with('episode.podcast')
                ->get(),
        ];
    }
}; ?>

<div class="min-h-screen bg-indigo-50 flex flex-col pt-8">
    <div class="flex justify-center items-center p-4">
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
    <div class="my-20">
        @if($listeningParties->isEmpty())
            <div>No listening parties started yet...🥲</div>
        @else
            @foreach($listeningParties as $listeningParty)
                <div wire:key="{{  $listeningParty->id }}">
                    <x-avatar src="{{ $listeningParty->episode->podcast->artwork_url }}" size="lg" rounded="full"/>
                    <p>{{ $listeningParty->name }}</p>
                    <p>{{ $listeningParty->episode->title }}</p>
                    <p>{{ $listeningParty->podcast->title }}</p>
                    <p>{{ $listeningParty->start_time }}</p>
                </div>
            @endforeach
        @endif
    </div>
</div>
