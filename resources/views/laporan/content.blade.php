<h1 style="text-align:center;">LAPORAN {{ strtoupper($jenisLabel) }}</h1>
<h2 style="text-align:center;">SURVEI KEPUASAN MASYARAKAT</h2>
<p style="text-align:center;color:#c43b2d;"><strong>BPBD KOTA BANDUNG</strong></p>
<p style="text-align:center;">Periode: {{ $labelPeriode }}</p>
<hr>

<h3>I. PENDAHULUAN</h3>
<p>
    Survei Kepuasan Masyarakat (SKM) merupakan kegiatan pengukuran secara komprehensif tentang tingkat
    kepuasan masyarakat terhadap kualitas layanan yang diberikan oleh Badan Penanggulangan Bencana Daerah
    (BPBD) Kota Bandung. Laporan ini mencakup periode <strong>{{ $labelPeriode }}</strong> dan disusun
    berdasarkan hasil pengisian kuesioner oleh <strong>{{ number_format($hasil['jumlah_responden']) }}
        responden</strong>.
</p>

<h3>II. HASIL PENGUKURAN</h3>
<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Unsur Pelayanan</th>
            <th>Responden</th>
            <th>Nilai Rata-rata</th>
            <th>Bobot</th>
            <th>Nilai Tertimbang</th>
            <th>Mutu</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($hasil['details'] as $detail)
            <tr>
                <td>{{ $detail['kode'] }}</td>
                <td>{{ $detail['nama_unsur'] }}</td>
                <td>{{ $detail['jumlah_responden'] }}</td>
                <td>{{ number_format($detail['nilai_rata_rata'], 3) }}</td>
                <td>{{ number_format($detail['bobot_nilai'], 3) }}</td>
                <td>{{ number_format($detail['nrr_tertimbang'], 3) }}</td>
                <td>{{ $detail['mutu_unsur'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="5" style="text-align:right;"><strong>Total IKM</strong></td>
            <td colspan="2">
                <strong>{{ number_format($hasil['nilai_ikm'], 2) }} ({{ $hasil['mutu_pelayanan'] }} /
                    {{ $hasil['kinerja_pelayanan'] }})</strong>
            </td>
        </tr>
    </tbody>
</table>

<h3>III. RINGKASAN</h3>
<p>
    Nilai IKM: <strong>{{ number_format($hasil['nilai_ikm'], 2) }}</strong> &mdash;
    Mutu Pelayanan: <strong>{{ $hasil['mutu_pelayanan'] }} ({{ $hasil['kinerja_pelayanan'] }})</strong>
</p>

<h3>IV. KESIMPULAN</h3>
<p>
    Berdasarkan hasil survei periode <strong>{{ $labelPeriode }}</strong>, BPBD Kota Bandung memperoleh
    Nilai IKM sebesar <strong>{{ number_format($hasil['nilai_ikm'], 2) }}</strong> dengan Mutu Pelayanan
    <strong>{{ $hasil['mutu_pelayanan'] }} ({{ $hasil['kinerja_pelayanan'] }})</strong>. Jumlah responden
    yang berpartisipasi sebanyak <strong>{{ number_format($hasil['jumlah_responden']) }} orang</strong>.
    @if ($unsurTertinggi)
        Unsur tertinggi adalah <strong>{{ $unsurTertinggi['nama_unsur'] }}
            ({{ number_format($unsurTertinggi['nilai_persen'], 2) }})</strong>,
    @endif
    @if ($unsurTerendah)
        sedangkan unsur yang masih perlu ditingkatkan adalah
        <strong>{{ $unsurTerendah['nama_unsur'] }} ({{ number_format($unsurTerendah['nilai_persen'], 2) }})</strong>.
    @endif
</p>

<h3>V. REKOMENDASI</h3>
<ol>
    @foreach ($rekomendasi as $item)
        <li>{{ $item }}</li>
    @endforeach
</ol>

<p style="margin-top:40px;">Bandung, {{ $tanggalCetak }}</p>
<p>Kepala BPBD Kota Bandung</p>
<br>
<br>
<br>
<p>____________________________</p>
