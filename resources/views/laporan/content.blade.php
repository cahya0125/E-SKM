@php
    $jumlahParameter = count($hasil['details']);
    $totalSkmTabel = array_sum($matriksResponden['skm_per_unsur'] ?? []);

    $formatNilai = static function ($nilai) {
        if ($nilai === null) {
            return '-';
        }

        $angka = (float) $nilai;

        if (abs($angka - round($angka)) < 0.00001) {
            return (string) (int) round($angka);
        }

        return number_format($angka, 2, '.', '');
    };

    $cellBorder = 'border:1px solid #000;padding:4px;';
    $nb = 'border:none;';
@endphp

{{-- ✅ CSS KHUSUS WORD: diabaikan DomPDF/mpdf, dibaca oleh Word --}}
<!--[if gte mso 9]>
<style type="text/css">
    div, p, h2, h3, table, td, th {
        margin-left:0 !important;
        margin-right:0 !important;
        text-indent:0 !important;
    }
    table {
        width:100% !important;
        border-collapse:collapse !important;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
    }
    td, th {
        padding:2px 3px !important;
        mso-line-height-rule:exactly;
    }
</style>
<![endif]-->

<div style="font-family:'Bookman Old Style', Georgia, serif;font-size:11px;line-height:1.3;color:#000;">
    <h2 style="text-align:center;margin:0;font-size:14px;font-weight:700;">PENGOLAHAN DATA SURVEI KEPUASAN MASYARAKAT (SKM)</h2>
    <p style="text-align:center;margin:2px 0 8px 0;font-size:12px;font-weight:700;">BPBD KOTA BANDUNG</p>

    <table style="width:100%;border-collapse:collapse;border:none;">
        <tr>
            <td style="{{ $nb }}width:130px;vertical-align:top;padding:2px 0;">Tujuan</td>
            <td style="{{ $nb }}width:14px;vertical-align:top;padding:2px 0;">:</td>
            <td style="{{ $nb }}padding:2px 0;">
                Dalam rangka meningkatkan mutu pelayanan publik, sebagai acuan mengukur tingkat kepuasan
                masyarakat sebagai pengguna layanan dan meningkatkan kualitas penyelenggaraan pelayanan
                publik yang dilakukan oleh unit pelayanan instansi pemerintah dalam melaksanakan pelayanan
                kepada masyarakat.
            </td>
        </tr>
        <tr>
            <td style="{{ $nb }}width:130px;padding:2px 0;">Periode</td>
            <td style="{{ $nb }}width:14px;padding:2px 0;">:</td>
            <td style="{{ $nb }}padding:2px 0;">{{ $labelPeriode }}</td>
        </tr>
        <tr>
            <td style="{{ $nb }}width:130px;padding:2px 0;">Tanggal</td>
            <td style="{{ $nb }}width:14px;padding:2px 0;">:</td>
            <td style="{{ $nb }}padding:2px 0;">{{ $hasil['tanggal_mulai'] ?? '-' }} s.d. {{ $hasil['tanggal_selesai'] ?? '-' }}</td>
        </tr>
        <tr>
            <td style="{{ $nb }}width:130px;padding:2px 0;">Survei</td>
            <td style="{{ $nb }}width:14px;padding:2px 0;">:</td>
            <td style="{{ $nb }}padding:2px 0;">Per Responden Per Parameter Survei</td>
        </tr>
        <tr>
            <td style="{{ $nb }}width:130px;vertical-align:top;padding:2px 0;">Metode</td>
            <td style="{{ $nb }}width:14px;vertical-align:top;padding:2px 0;">:</td>
            <td style="{{ $nb }}padding:2px 0;">
                Peraturan Menteri Pendayagunaan Aparatur Negara dan Reformasi Birokrasi Nomor 14 Tahun 2017
                tentang Pedoman Penyusunan Survei Kepuasan Masyarakat Unit Pelayanan Instansi Pemerintah.
            </td>
        </tr>
        <tr>
            <td style="{{ $nb }}width:130px;padding:2px 0;">Jumlah Responden</td>
            <td style="{{ $nb }}width:14px;padding:2px 0;">:</td>
            <td style="{{ $nb }}padding:2px 0;">{{ number_format($hasil['jumlah_responden']) }} orang</td>
        </tr>
        <tr>
            <td style="{{ $nb }}width:130px;vertical-align:top;padding:2px 0;">Jumlah Parameter</td>
            <td style="{{ $nb }}width:14px;vertical-align:top;padding:2px 0;">:</td>
            <td style="{{ $nb }}padding:2px 0;vertical-align:top;">
                {{ $jumlahParameter }} Parameter<br>
                @foreach ($hasil['details'] as $i => $detail)
                    &nbsp;&nbsp;&nbsp;&nbsp;{{ $i + 1 }}.&nbsp;&nbsp;{{ $detail['nama_unsur'] }}<br>
                @endforeach
            </td>
        </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;border:none;margin-top:14px;">
        <tr>
            <td style="{{ $nb }}width:20%;"></td>
            <td style="width:60%;border:1px solid #000;padding:8px 10px;text-align:center;vertical-align:middle;">
                <table style="width:100%;border-collapse:collapse;border:none;">
                    <tr>
                        <td style="{{ $nb }}width:15%;text-align:right;vertical-align:middle;">
                            <strong style="font-size:12px;">SKM</strong> =
                        </td>
                        <td style="{{ $nb }}width:65%;vertical-align:middle;padding:0 6px;">
                            <table style="width:100%;border-collapse:collapse;border:none;">
                                <tr>
                                    <td style="{{ $nb }}border-bottom:1px solid #000 !important;padding:0 4px 2px 4px;text-align:center;">
                                        Total Nilai Persepsi Responden Per Parameter
                                    </td>
                                </tr>
                                <tr>
                                    <td style="{{ $nb }}padding:3px 4px 0 4px;text-align:center;">
                                        Total Parameter Yang Terisi
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="{{ $nb }}width:20%;text-align:left;vertical-align:middle;">
                            x Bobot
                        </td>
                    </tr>
                </table>
            </td>
            <td style="{{ $nb }}width:20%;"></td>
        </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;border:none;margin-top:10px;">
        <tr>
            <td style="{{ $nb }}width:130px;vertical-align:top;padding:2px 0;">Analisa Perhitungan</td>
            <td style="{{ $nb }}width:14px;vertical-align:top;padding:2px 0;">:</td>
            <td style="{{ $nb }}padding:2px 0;">
                Bobot = 1 / Jumlah Parameter<br>
                = (1 / {{ $jumlahParameter }})<br>
                = {{ number_format(1 / max($jumlahParameter, 1), 3) }}
            </td>
        </tr>
    </table>
    <br>
    <table style="width:100%;border-collapse:collapse;border:none;margin-top:12px;">
        <tr>
            <td style="{{ $nb }}width:35%;"></td>
            <td style="width:30%;border:1px solid #000;padding:5px 12px;text-align:center;"><strong>IKM Unit Pelayanan x 20</strong></td>
            <td style="{{ $nb }}width:35%;"></td>
        </tr>
    </table>
    <br>
    <table style="width:100%;border-collapse:collapse;border:none;margin-top:8px;">
        <tr>
            <th style="{{ $cellBorder }}background:#c6e0b4;text-align:center;">Nilai Persepsi</th>
            <th style="{{ $cellBorder }}background:#c6e0b4;text-align:center;">Nilai Interval</th>
            <th style="{{ $cellBorder }}background:#c6e0b4;text-align:center;">Nilai Interval Konversi</th>
            <th style="{{ $cellBorder }}background:#c6e0b4;text-align:center;">Kategori Mutu Pelayanan</th>
            <th style="{{ $cellBorder }}background:#c6e0b4;text-align:center;">Mutu Pelayanan</th>
        </tr>
        <tr>
            <td style="{{ $cellBorder }}text-align:center;">1</td>
            <td style="{{ $cellBorder }}">1.00 - 1.75</td>
            <td style="{{ $cellBorder }}">25.00 - 64.99</td>
            <td style="{{ $cellBorder }}text-align:center;">D</td>
            <td style="{{ $cellBorder }}">Tidak Baik</td>
        </tr>
        <tr>
            <td style="{{ $cellBorder }}text-align:center;">2</td>
            <td style="{{ $cellBorder }}">1.76 - 2.50</td>
            <td style="{{ $cellBorder }}">65.00 - 76.60</td>
            <td style="{{ $cellBorder }}text-align:center;">C</td>
            <td style="{{ $cellBorder }}">Kurang Baik</td>
        </tr>
        <tr>
            <td style="{{ $cellBorder }}text-align:center;">3</td>
            <td style="{{ $cellBorder }}">2.51 - 3.25</td>
            <td style="{{ $cellBorder }}">76.61 - 88.30</td>
            <td style="{{ $cellBorder }}text-align:center;">B</td>
            <td style="{{ $cellBorder }}">Baik</td>
        </tr>
        <tr>
            <td style="{{ $cellBorder }}text-align:center;">4</td>
            <td style="{{ $cellBorder }}">3.26 - 4.00</td>
            <td style="{{ $cellBorder }}">88.31 - 100.00</td>
            <td style="{{ $cellBorder }}text-align:center;">A</td>
            <td style="{{ $cellBorder }}">Sangat Baik</td>
        </tr>
    </table>

    <div style="page-break-before:always;"></div>

    <h3 style="text-align:center;margin:0 0 8px 0;font-size:13px;font-weight:700;">PENGOLAHAN INDEKS KEPUASAN PERRESPONDEN DAN UNSUR PELAYANAN</h3>

    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="{{ $cellBorder }}width:16%;font-weight:700;">Jumlah Responden</td>
            <td style="{{ $cellBorder }}width:8%;text-align:center;font-weight:700;">{{ $hasil['jumlah_responden'] }}</td>
            <td style="{{ $cellBorder }}text-align:center;font-weight:700;" colspan="{{ $jumlahParameter }}">Parameter</td>
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellBorder }}font-weight:700;text-align:center;">No. Urut Responden</td>
            @for ($i = 1; $i <= $jumlahParameter; $i++)
                <td style="{{ $cellBorder }}width:{{ number_format(76 / max($jumlahParameter, 1), 2, '.', '') }}%;text-align:center;font-style:italic;font-weight:700;">P{{ $i }}</td>
            @endfor
        </tr>
        @foreach ($matriksResponden['rows'] as $row)
            <tr>
                <td colspan="2" style="{{ $cellBorder }}text-align:center;">{{ $row['nomor'] }}.</td>
                @foreach ($row['nilai'] as $nilai)
                    <td style="{{ $cellBorder }}text-align:center;">{{ $formatNilai($nilai) }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="{{ $cellBorder }}background:#fff2cc;font-weight:700;">Nilai Per Parameter</td>
            @foreach ($matriksResponden['total_per_unsur'] as $total)
                <td style="{{ $cellBorder }}background:#fff2cc;text-align:center;font-weight:700;">{{ $formatNilai($total) }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellBorder }}border-top:2px solid #000;font-weight:700;">Nilai Rata-rata (NRR)<br>Per Parameter</td>
            @foreach ($matriksResponden['nrr_per_unsur'] as $nrr)
                <td style="{{ $cellBorder }}border-top:2px solid #000;text-align:center;">{{ number_format($nrr, 3, '.', '') }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellBorder }}font-weight:700;">BOBOT</td>
            @foreach ($matriksResponden['bobot_per_unsur'] as $bobot)
                <td style="{{ $cellBorder }}text-align:center;">{{ number_format($bobot, 3, '.', '') }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellBorder }}font-weight:700;">Survey Kepuasan<br>Masyarakat (SKM)</td>
            @foreach ($matriksResponden['skm_per_unsur'] as $skm)
                <td style="{{ $cellBorder }}text-align:center;">{{ number_format($skm, 3, '.', '') }}</td>
            @endforeach
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellBorder }}font-weight:700;">Indeks Kepuasan Masyarakat (IKM)</td>
            <td colspan="{{ $jumlahParameter }}" style="{{ $cellBorder }}text-align:center;"><strong>{{ number_format($hasil['nilai_ikm'], 2, '.', '') }}</strong></td>
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellBorder }}font-weight:700;">Mutu Pelayanan</td>
            <td colspan="{{ $jumlahParameter }}" style="{{ $cellBorder }}text-align:center;"><strong>{{ $hasil['mutu_pelayanan'] }}</strong></td>
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellBorder }}font-weight:700;">Kategori Penilaian Kepuasan Pelayanan</td>
            <td colspan="{{ $jumlahParameter }}" style="{{ $cellBorder }}text-align:center;font-style:italic;"><strong>{{ $hasil['kinerja_pelayanan'] }}</strong></td>
        </tr>
    </table>

    <h3 style="margin:12px 0 6px 0;font-size:12px;font-weight:700;">KESIMPULAN</h3>

    <p style="margin:0 0 4px 0;">
        1. Berdasarkan hasil perhitungan nilai konversi penilaian SKM, maka kategori penilaian kepuasan pelayanan yaitu:
    </p>
    <table style="width:100%;border-collapse:collapse;border:none;margin:6px 0 12px 0;">
        <tr>
            <td style="border:1px solid #000;padding:6px 18px;font-size:18px;text-align:center;font-style:italic;"><strong>{{ strtoupper($hasil['kinerja_pelayanan']) }}</strong></td>
        </tr>
    </table>

    @if ($unsurTertinggi && $unsurTerendah)
        <p style="margin:0;">
            2. Persepsi tertinggi terhadap kepuasan pelayanan adalah <strong>{{ $unsurTertinggi['nama_unsur'] }}</strong>,
            sedangkan persepsi terendah terhadap kepuasan pelayanan adalah <strong>{{ $unsurTerendah['nama_unsur'] }}</strong>.
        </p>
    @endif

    @if (!empty($chartImageUrl))
        <p style="text-align:center;margin:12px 0;">
            <img src="{{ $chartImageUrl }}" width="360" height="216" style="width:360px;">
        </p>
    @endif

    <table style="width:100%;border-collapse:collapse;border:none;margin-top:18px;">
        <tr>
            <td style="{{ $nb }}width:58%;"></td>
            <td style="{{ $nb }}text-align:center;">
                <strong>KEPALA PELAKSANA BADAN<br>PENANGGULANGAN BENCANA DAERAH</strong>
                <br><br><br><br><br><br>
                <u>Cahyahadi</u>
                <br>
                NIP. 
            </td>
        </tr>
    </table>
</div>