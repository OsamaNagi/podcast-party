<?php

use App\Models\ListeningParty;
use Livewire\Volt\Component;

new class extends Component {
    public ListeningParty $listeningParty;
    public isFinished = false;

    public function mount(ListeningParty $listeningParty): void
    {
        
        $this->listeningParty = $listeningParty;
    }
}; ?>

<div class="!font-mono" x-data="{
                audio: null,
                isLoading: true,
                isPlaying: false,
                isLive: false,
                isReady: false,
                currentTime: 0,
                countDownText: '',
                startTimestamp: {{ $listeningParty->start_time->timestamp }},
                endTimestamp: {{ $listeningParty->end_time ? $listeningParty->end_time->timestamp : 'null' }},

                initializeAudioPlayer() {
                    this.audio = this.$refs.audioPlayer;
                    this.audio.addEventListener('loadedmetadata', () => {
                        this.isLoading = false;
                        this.checkAndUpdate();
                    });

                    this.audio.addEventListener('timeupdate', () => {
                        this.currentTime = this.audio.currentTime;
                    });

                    this.audio.addEventListener('play', () => {
                        this.isPlaying = true;
                        this.isReady = true;
                    });

                    this.audio.addEventListener('pause', () => {
                        this.isPlaying = false;
                    });
                },

                finishListeningParty() {

                },

                checkAndUpdate() {
                    const now = Math.floor(Date.now() / 1000);
                    const timeUntilStart = this.startTimestamp - now;

                    if (timeUntilStart <= 0) {
                        this.isLive = true;
                        if (!this.isPlaying) {
                            this.isLive = true;
                            this.playAudio();
                        }
                    } else {
                        const days = Math.floor(timeUntilStart / 86400);
                        const hours = Math.floor((timeUntilStart % 86400) / 3600);
                        const minutes = Math.floor((timeUntilStart % 3600) / 60);
                        const seconds = timeUntilStart % 60;
                        this.countDownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                    }
                },

                playAudio() {
                    const now = Math.floor(Date.now() / 1000);
                    this.audio.currentTime = Math.max(0, now - this.startTimestamp);
                    this.audio.play().catch(error => {
                        console.error('Playback error:', error);
                        this.isPlaying = false;
                        this.isReady = false;
                    });
                },

                joinAndBeReady() {
                    this.isReady = true;
                    if (this.isLive) {
                        this.playAudio();
                    }
                },

                formatTime(seconds) {
                    const minutes = Math.floor(seconds / 60);
                    const remainingSeconds = Math.floor(seconds % 60);

                    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
                }
            }" x-init="initializeAudioPlayer()">
    @if($listeningParty->end_time === null)
        <div class="flex items-center justify-center" wire:poll.5s>
            <h1 class="text-3xl font-mono font-bold text-center">
                Creating your <span class="font-bold"> {{ $listeningParty->name }} </span>
                Listening Party....
            </h1>
        </div>
    @else
        <audio x-ref="audioPlayer" src="{{ $listeningParty->episode->media_url }}"
               preload="auto"></audio>

        <div x-show="!isLive" class="flex items-center justify-center min-h-screen bg-indigo-50">
            <div class="w-full max-w-2xl shadow-lg rounded-lg bg-white p-8">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <x-avatar src="{{ $listeningParty->episode->podcast->artwork_url }}"
                                  size="lg"
                                  rounded="sm" alt="Podcast artwork"/>
                    </div>
                    <div class="flex justify-between w-full">
                        <div class="flex-1 min-w-0 font-mono">
                            <p class="text-[0.9rem] font-semibold text-slate-900 truncate">{{ $listeningParty->name }}</p>
                            <div class="mt-2">
                                <p class="text-xs truncate text-slate-600 max-w-xs">{{ $listeningParty->episode->title }}</p>
                                <p class="text-xs uppercase text-slate-500">{{ $listeningParty->podcast->title }}</p>
                            </div>
                            <div class="text-xs text-slate-600 mt-1">

                            </div>
                        </div>
                    </div>
                    <div class="text-slate-600 font-bold flex flex-col items-center">
                        <p>Starts in:</p>
                        <p class="whitespace-nowrap" x-text="countDownText"></p>
                    </div>
                </div>

                <x-button x-show="!isReady" @click="joinAndBeReady()" class="w-full font-bold mt-8">Join and be ready</x-button>

                <h2 x-show="isReady" class="mt-8 text-center text-xl font-mono font-bold text-blue-600">
                    Ready to start the party, stay tuned. 🥵🔥
                </h2>
            </div>
        </div>

        <div x-show="isLive">
            <div>{{ $listeningParty->podcast->title }}</div>
            <div>{{ $listeningParty->episode->title }}</div>
            <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
            <div>Start Time: {{ $listeningParty->start_time }}</div>
            <div x-show="isLoading">Loading....</div>
            <x-button x-show="!isReady" @click="joinAndBeReady()" class="w-full font-bold mt-8">Join and be ready</x-button>
        </div>
    @endif
</div>
