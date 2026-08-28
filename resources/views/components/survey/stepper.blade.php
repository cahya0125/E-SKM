@props(['current' => 1])

@php
    $steps = [1 => 'Data Responden', 2 => 'Penilaian', 3 => 'Kritik & Saran', 4 => 'Selesai'];
@endphp

<div class="flex items-start justify-center">
    @foreach ($steps as $i => $label)
        <div class="flex items-start">
            <div class="flex w-20 flex-col items-center">
                @if ($current > $i)
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-green-600 text-white">
                        <i class="fa-solid fa-check"></i>
                    </span>
                @elseif ($current === $i)
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#b3402a] font-semibold text-white">{{ $i }}</span>
                @else
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 font-semibold text-slate-400">{{ $i }}</span>
                @endif
                <span class="mt-2 text-center text-xs font-semibold leading-4 {{ $current > $i ? 'text-green-700' : ($current === $i ? 'text-[#b3402a]' : 'text-slate-400') }}">
                    {{ $label }}
                </span>
            </div>
            @if ($i < count($steps))
                <div class="mt-[17px] h-0.5 w-10 sm:w-16 {{ $current > $i ? 'bg-green-600' : 'bg-slate-300' }}"></div>
            @endif
        </div>
    @endforeach
</div>