@extends('layouts.survey')

@section('content')
@php
    $defaultOptions = ['Sangat Tidak Sesuai', 'Tidak Sesuai', 'Cukup Sesuai', 'Sesuai', 'Sangat Sesuai'];
@endphp

<div class="mx-auto max-w-3xl px-4 py-10">
    <x-survey.stepper :current="2" />

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('survey.penilaian.save') }}" class="mt-8"
        x-data="{
            current: 0,
            timer: null,
            ids: @js($unsurs->pluck('id')->values()),
            answers: @js((object) $jawaban),
            get answered() { return !!this.answers[this.ids[this.current]] },
            next() { if (this.current < this.ids.length - 1) this.current++ },
            prev() { if (this.current > 0) this.current-- },
            autoNext() {
                // Pertanyaan terakhir tetap lewat tombol (lebih aman),
                // pertanyaan 1–8 langsung maju otomatis.
                if (this.current >= this.ids.length - 1) return;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this.current++, 350);
            }
        }">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm font-bold uppercase tracking-wide text-[#b3402a]">Survei Kepuasan Masyarakat</p>
                <p class="text-sm font-semibold text-slate-500" x-text="(current + 1) + ' / ' + ids.length"></p>
            </div>

            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-[#b3402a] transition-all"
                     :style="'width: ' + ((current + 1) / ids.length) * 100 + '%'"></div>
            </div>

            @foreach ($unsurs as $i => $unsur)
                @php
                    $options = array_combine(range(1, 5), array_values($unsur->opsi_jawaban ?: $defaultOptions));
                @endphp
                <div x-show="current === {{ $i }}" x-cloak class="mt-8">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Pertanyaan {{ $i + 1 }} dari {{ $unsurs->count() }}
                    </p>
                    <h2 class="mt-3 text-lg font-bold text-slate-800 sm:text-xl">{{ $unsur->pertanyaan }}</h2>

                    <div class="mt-6 space-y-3">
                        @foreach ($options as $nilai => $label)
                            <label class="flex cursor-pointer items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 transition
                                          has-[:checked]:border-[#b3402a] has-[:checked]:bg-red-50 has-[:checked]:ring-1 has-[:checked]:ring-[#b3402a]">
                                <input type="radio" name="jawaban[{{ $unsur->id }}]" value="{{ $nilai }}"
                                       x-model="answers['{{ $unsur->id }}']" @change="autoNext()" class="h-5 w-5 accent-[#b3402a]">
                                <span class="text-sm text-slate-700">{{ $nilai }}. {{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-8 flex items-center gap-3">
                <a href="{{ route('survey.responden') }}" x-show="current === 0"
                   class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">← Sebelumnya</a>
                <button type="button" x-show="current > 0" x-cloak @click="prev"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">← Sebelumnya</button>

                <button type="button" x-show="current < ids.length - 1" @click="next" :disabled="!answered"
                        class="flex-1 rounded-xl bg-[#b3402a] px-5 py-3 text-sm font-bold text-white hover:bg-[#9c3521] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
                    Berikutnya →
                </button>
                <button type="submit" x-show="current === ids.length - 1" x-cloak :disabled="!answered"
                        class="flex-1 rounded-xl bg-[#b3402a] px-5 py-3 text-sm font-bold text-white hover:bg-[#9c3521] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
                    Berikutnya →
                </button>
            </div>
        </div>

        {{-- Dot pagination --}}
        <div class="mt-6 flex justify-center gap-1.5">
            <template x-for="(id, i) in ids" :key="id">
                <button type="button" @click="current = i" class="h-2 rounded-full transition-all"
                        :class="i === current ? 'w-6 bg-[#b3402a]' : (answers[id] ? 'w-2 bg-[#b3402a]/50' : 'w-2 bg-slate-300')">
                </button>
            </template>
        </div>
    </form>
</div>
@endsection