@extends('layouts.survey')

@section('content')
@php
    $pendidikanOptions = ['SD / Sederajat', 'SMP / Sederajat', 'SMA / Sederajat', 'D1 / D2 / D3', 'S1 / D4', 'S2 / S3'];
    $pekerjaanOptions  = ['Pelajar / Mahasiswa', 'PNS / TNI / Polri', 'Pegawai Swasta', 'Wiraswasta', 'Petani / Nelayan', 'Buruh', 'Ibu Rumah Tangga', 'Tidak Bekerja', 'Lainnya'];
    $inputClass        = 'mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-700 focus:border-[#b3402a] focus:outline-none focus:ring-1 focus:ring-[#b3402a]';
@endphp

<div class="mx-auto max-w-3xl px-4 py-10">
    <x-survey.stepper :current="1" />

    <form method="POST" action="{{ route('survey.responden.save') }}"
          class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
        @csrf

        <h1 class="text-2xl font-extrabold text-slate-800">Data Responden</h1>
        <p class="mt-2 text-slate-500">Lengkapi informasi berikut. Data ini bersifat rahasia dan hanya digunakan untuk keperluan statistik.</p>

        <div class="mt-8 space-y-6">
            {{-- Nama (opsional) --}}
            <div>
                <label for="nama" class="text-sm font-semibold text-slate-700">
                    Nama Lengkap <span class="font-normal text-slate-400">(opsional)</span>
                </label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}"
                       placeholder="Masukkan nama lengkap Anda (opsional)" class="{{ $inputClass }}">
                @error('nama') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Jenis kelamin --}}
            <div>
                <span class="text-sm font-semibold text-slate-700">Jenis Kelamin <span class="text-[#b3402a]">*</span></span>
                <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-3.5 font-semibold text-slate-600 transition
                                  has-[:checked]:border-[#b3402a] has-[:checked]:bg-red-50 has-[:checked]:text-[#b3402a] has-[:checked]:ring-1 has-[:checked]:ring-[#b3402a]">
                        <input type="radio" name="jenis_kelamin" value="L" class="sr-only" {{ old('jenis_kelamin') === 'L' ? 'checked' : '' }}>
                        <i class="fa-solid fa-mars"></i> Laki-laki
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-3.5 font-semibold text-slate-600 transition
                                  has-[:checked]:border-[#b3402a] has-[:checked]:bg-red-50 has-[:checked]:text-[#b3402a] has-[:checked]:ring-1 has-[:checked]:ring-[#b3402a]">
                        <input type="radio" name="jenis_kelamin" value="P" class="sr-only" {{ old('jenis_kelamin') === 'P' ? 'checked' : '' }}>
                        <i class="fa-solid fa-venus"></i> Perempuan
                    </label>
                </div>
                @error('jenis_kelamin') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Usia --}}
                <div>
                    <label for="usia" class="text-sm font-semibold text-slate-700">Usia <span class="text-[#b3402a]">*</span></label>
                    <input type="number" id="usia" name="usia" min="1" max="120" value="{{ old('usia') }}" class="{{ $inputClass }}">
                    @error('usia') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Pendidikan --}}
                <div>
                    <label for="pendidikan" class="text-sm font-semibold text-slate-700">Pendidikan <span class="text-[#b3402a]">*</span></label>
                    <select id="pendidikan" name="pendidikan" class="{{ $inputClass }} bg-white">
                        <option value="">— Pilih pendidikan —</option>
                        @foreach ($pendidikanOptions as $opt)
                            <option value="{{ $opt }}" {{ old('pendidikan') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('pendidikan') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Pekerjaan --}}
            <div>
                <label for="pekerjaan" class="text-sm font-semibold text-slate-700">Pekerjaan <span class="text-[#b3402a]">*</span></label>
                <select id="pekerjaan" name="pekerjaan" class="{{ $inputClass }} bg-white">
                    <option value="">— Pilih pekerjaan —</option>
                    @foreach ($pekerjaanOptions as $opt)
                        <option value="{{ $opt }}" {{ old('pekerjaan') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('pekerjaan') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- No HP (opsional) --}}
            <div>
                <label for="no_hp" class="text-sm font-semibold text-slate-700">
                    Nomor HP <span class="font-normal text-slate-400">(opsional)</span>
                </label>
                <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" class="{{ $inputClass }}">
                @error('no_hp') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('survey.mulai') }}"
               class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">← Kembali</a>
            <button type="submit"
                    class="flex-1 rounded-xl bg-[#b3402a] px-6 py-3 font-bold text-white hover:bg-[#9c3521]">Lanjutkan →</button>
        </div>
    </form>
</div>
@endsection