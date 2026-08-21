<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	@php
		$pageTitles = ['dashboard' => 'Dashboard', 'pengguna' => 'Pengguna', 'periode-survei' => 'Periode Survei', 'responden' => 'Responden', 'hasil-survei' => 'Hasil Survei', 'kritik-saran' => 'Kritik & Saran', 'hasil-ikm' => 'Hasil IKM', 'grafik' => 'Grafik', 'laporan' => 'Laporan'];
		$currentPage = request()->routeIs('admin.users') ? 'pengguna' : (request()->routeIs('admin.respondens') ? 'responden' : (request()->routeIs('admin.grafik') ? 'grafik' : request('page', 'dashboard')));
		$currentPageTitle = $pageTitles[$currentPage] ?? $pageTitles['dashboard'];
	@endphp
	<title>@yield('title', $currentPageTitle . ' · e-SKM')</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-[#172b4d] antialiased">
	<x-admin.sidebar />
	<x-alert />
	<div class="ml-14 min-h-screen lg:ml-48">
		<header class="flex h-16 items-center justify-between bg-white px-4 sm:px-6">
			<div><h1 class="text-base font-semibold">{{ $currentPageTitle }}</h1><p class="mt-1 text-[10px] text-slate-400">Selamat datang, {{ Auth::user()->name }} · {{ now()->locale('id')->translatedFormat('l, d F Y') }}</p></div>
			<div class="flex items-center gap-4"><span class="relative inline-flex text-slate-500" aria-label="Notifikasi"><i class="fa-solid fa-bell h-5 w-5 text-center" aria-hidden="true"></i><i class="absolute -right-0.5 top-0 h-1 w-1 rounded-full bg-[#c43b2d]"></i></span><span class="grid h-8 w-8 place-content-center rounded-full bg-[#c43b2d] text-xs font-semibold text-white">A</span></div>
		</header>
		<main class="p-4 sm:p-5">@yield('content')</main>
	</div>
</body>
</html>
