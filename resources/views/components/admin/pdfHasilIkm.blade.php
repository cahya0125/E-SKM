<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil IKM</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        p.subtitle { margin: 0 0 16px; color: #64748b; }

        .summary { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary td { width: 25%; text-align: center; padding: 10px; border: 1px solid #e2e8f0; }
        .summary .label { font-size: 9px; text-transform: uppercase; color: #94a3b8; }
        .summary .value { font-size: 18px; font-weight: bold; color: #c43b2d; margin-top: 4px; }

        table.detail { width: 100%; border-collapse: collapse; }
        table.detail th, table.detail td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        table.detail th { background: #f8fafc; text-transform: uppercase; font-size: 9px; color: #64748b; }
        table.detail td.number { text-align: right; }
        table.detail tfoot td { font-weight: bold; background: #f8fafc; }

        .footer { margin-top: 24px; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    @php
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $labelPeriode = match ($hasil['tipe']) {
            'bulanan' => ($bulanList[$hasil['bulan']] ?? '-') . ' ' . $hasil['tahun'],
            'triwulanan' => 'Triwulan ' . $hasil['triwulan'] . ' ' . $hasil['tahun'],
            default => 'Tahun ' . $hasil['tahun'],
        };
    @endphp

    <h1>Hasil Indeks Kepuasan Masyarakat (IKM)</h1>
    <p class="subtitle">
        BPBD Kota Bandung &middot; {{ $labelPeriode }}
        ({{ $hasil['mulai']->translatedFormat('d M Y') }} - {{ $hasil['selesai']->translatedFormat('d M Y') }})
    </p>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Nilai IKM</div>
                <div class="value">{{ number_format($hasil['nilai_ikm'], 2) }}</div>
            </td>
            <td>
                <div class="label">Mutu Pelayanan</div>
                <div class="value">{{ $hasil['mutu_pelayanan'] }}</div>
            </td>
            <td>
                <div class="label">Kinerja Pelayanan</div>
                <div class="value">{{ $hasil['kinerja_pelayanan'] }}</div>
            </td>
            <td>
                <div class="label">Jumlah Responden</div>
                <div class="value">{{ number_format($hasil['jumlah_responden']) }}</div>
            </td>
        </tr>
    </table>

    <table class="detail">
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
                    <td class="number">{{ number_format($detail['jumlah_responden']) }}</td>
                    <td class="number">{{ number_format($detail['nilai_rata_rata'], 3) }}</td>
                    <td class="number">{{ number_format($detail['bobot_nilai'], 3) }}</td>
                    <td class="number">{{ number_format($detail['nrr_tertimbang'], 3) }}</td>
                    <td>{{ $detail['mutu_unsur'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;">Total IKM</td>
                <td class="number">{{ number_format($hasil['nilai_ikm'], 2) }}</td>
                <td>{{ $hasil['mutu_pelayanan'] }} / {{ $hasil['kinerja_pelayanan'] }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="footer">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &middot; Sistem e-SKM BPBD Kota Bandung</p>
</body>
</html>