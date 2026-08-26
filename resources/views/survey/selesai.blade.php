@extends('layouts.survey')

@section('content')
<div class="mx-auto flex max-w-2xl flex-col items-center px-4 py-20 text-center">
    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-green-100 text-5xl text-green-600">
        <i class="fa-regular fa-circle-check"></i>
    </div>

    <h1 class="mt-8 text-3xl font-extrabold text-slate-800">Terima Kasih!</h1>
    <p class="mt-4 text-slate-600">Survei Kepuasan Masyarakat Anda berhasil dikirim.</p>
    <p class="mt-3 max-w-md text-sm text-slate-400">
        Partisipasi Anda membantu BPBD Kota Bandung meningkatkan kualitas pelayanan kepada masyarakat Kota Bandung.
    </p>

    <span class="mt-8 inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-semibold text-[#b3402a]">
        <i class="fa-solid fa-hexagon"></i> BPBD Kota Bandung
    </span>

    <a href="{{ route('home') }}"
       class="mt-8 rounded-xl bg-[#1f2a44] px-6 py-3.5 font-bold text-white hover:bg-[#16203a]">
        Kembali ke Beranda
    </a>
</div>
@endsection