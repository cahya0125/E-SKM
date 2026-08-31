@extends('errors.layout')

@section('code', '401')
@section('title', 'Belum Masuk')
@section('message', 'Anda harus masuk terlebih dahulu untuk mengakses halaman ini.')
@section('icon')<i class="fa-regular fa-user"></i>@endsection

@section('actions')
    <a href="{{ url('/login') }}"
       class="rounded-xl bg-[#b3402a] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#9c3521]">
        Masuk sebagai Admin
    </a>
    <a href="{{ url('/') }}"
       class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
        Kembali ke Beranda
    </a>
@endsection