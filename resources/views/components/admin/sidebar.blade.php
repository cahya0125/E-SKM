@php
	$currentPage = request()->routeIs('admin.users') ? 'pengguna' : request('page', 'dashboard');
@endphp

<aside class="admin-sidebar">
	<div class="admin-brand">
		<div class="admin-brand-mark"><img src="{{ asset('assets/img/bpbd-logo.png') }}" alt="Logo BPBD Kota Bandung"></div>
		<div><strong>e-SKM</strong><span>BPBD Kota Bandung</span></div>
	</div>

	<nav class="admin-nav" aria-label="Navigasi admin">
		@foreach ([
			['dashboard', 'Dashboard', '⌗'], ['pengguna', 'Pengguna', '♙'],
			['periode-survei', 'Periode Survei', '▣'], ['responden', 'Responden', '♧'],
			['hasil-survei', 'Hasil Survei', '▧'], ['kritik-saran', 'Kritik & Saran', '▱'],
			['hasil-ikm', 'Hasil IKM', '⌁'], ['grafik', 'Grafik', '▥'], ['laporan', 'Laporan', '▤'],
		] as [$slug, $label, $icon])
			<a href="{{ $slug === 'pengguna' ? route('admin.users') : route('admin.dashboard', ['page' => $slug]) }}" class="admin-nav-item {{ $currentPage === $slug ? 'is-active' : '' }}">
				<span class="admin-nav-icon">{{ $icon }}</span><span>{{ $label }}</span>
			</a>
		@endforeach
	</nav>

	<div class="admin-account">
		<div class="admin-user"><span class="admin-avatar">A</span><div><strong>Admin BPBD</strong><span>Superadmin</span></div></div>
		<a href="#" class="admin-logout"><span>↪</span> Keluar</a>
	</div>
</aside>
