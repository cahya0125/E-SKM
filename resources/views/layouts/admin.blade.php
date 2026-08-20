<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	@php
		$pageTitles = ['dashboard' => 'Dashboard', 'pengguna' => 'Pengguna', 'periode-survei' => 'Periode Survei', 'responden' => 'Responden', 'hasil-survei' => 'Hasil Survei', 'kritik-saran' => 'Kritik & Saran', 'hasil-ikm' => 'Hasil IKM', 'grafik' => 'Grafik', 'laporan' => 'Laporan'];
		$currentPage = request()->routeIs('admin.users') ? 'pengguna' : request('page', 'dashboard');
		$currentPageTitle = $pageTitles[$currentPage] ?? $pageTitles['dashboard'];
	@endphp
	<title>@yield('title', $currentPageTitle . ' · e-SKM')</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell antialiased">
	<x-admin.sidebar />
	<div class="admin-main">
		<header class="admin-header">
			<div><h1>{{ $currentPageTitle }}</h1><p>Selamat datang, Admin BPBD · Rabu, 12 Agustus 2026</p></div>
			<div class="admin-header-actions"><span class="admin-bell" aria-label="Notifikasi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" /></svg><i></i></span><span class="admin-header-avatar">A</span></div>
		</header>
		<main class="admin-content">@yield('content')</main>
	</div>
</body>
</html>
