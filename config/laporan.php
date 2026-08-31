<?php

return [
    // Diisi nama pejabat yang menandatangani laporan (Kepala Pelaksana BPBD)
    'pimpinan_nama' => env('LAPORAN_PIMPINAN_NAMA', 'Nama Pimpinan Belum Diisi'),

    // Diisi pangkat/golongan pejabat yang bersangkutan
    'pimpinan_pangkat' => env('LAPORAN_PIMPINAN_PANGKAT', 'Pangkat Belum Diisi'),

    // Diisi NIP pejabat yang bersangkutan
    'pimpinan_nip' => env('LAPORAN_PIMPINAN_NIP', '-'),
];