@extends('errors.layout')

@section('code', '429')
@section('title', 'Terlalu Banyak Permintaan')
@section('message', 'Anda mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu beberapa saat sebelum mencoba lagi.')
@section('icon')<i class="fa-solid fa-clock"></i>@endsection