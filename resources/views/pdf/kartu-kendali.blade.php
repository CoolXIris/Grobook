<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Kendali - {{ $publication->number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #111; margin: 24px; }
        h1 { font-size: 13px; text-align: center; margin: 0; }
        h2 { font-size: 11px; text-align: center; margin: 2px 0 14px; }
        h3 { font-size: 10px; margin: 14px 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 4px 5px; vertical-align: top; }
        th { background: #e8e8e8; font-size: 9px; }
        .identitas td { border: none; padding: 2px 4px; }
        .identitas .label { width: 140px; font-weight: bold; }
        .section-row td { background: #d6e4f0; font-weight: bold; font-size: 9.5px; }
        .group-row td { background: #f0f0f0; font-weight: bold; font-style: italic; }
        .center { text-align: center; }
        .check { text-align: center; font-size: 11px; width: 46px; }
        .no { width: 24px; text-align: center; }
        .ttd { height: 34px; }
        .legend { margin-top: 10px; font-size: 8.5px; }
        .catatan { margin-top: 12px; font-size: 8.5px; }
    </style>
</head>
<body>
    <h1>KARTU KENDALI PEMBUATAN PUBLIKASI</h1>
    <h2>BPS PROVINSI SUMATERA SELATAN</h2>

    <h3>IDENTITAS PUBLIKASI</h3>
    <table class="identitas">
        <tr><td class="label">NOMOR PENGAJUAN</td><td>: {{ $publication->number }}</td></tr>
        <tr><td class="label">JUDUL PUBLIKASI</td><td>: {{ $publication->title }}</td></tr>
        @if ($publication->title_en)
            <tr><td class="label">JUDUL INGGRIS</td><td>: {{ $publication->title_en }}</td></tr>
        @endif
        <tr><td class="label">TIPE PUBLIKASI</td><td>: {{ $publication->type->label() }}</td></tr>
        <tr><td class="label">TIM</td><td>: {{ $publication->team->name }}</td></tr>
        <tr><td class="label">ESTIMASI TANGGAL RILIS</td><td>: {{ $publication->release_date->translatedFormat('d F Y') }}</td></tr>
    </table>

    <h3>PROGRES PEKERJAAN</h3>
    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th style="width: 130px;">Uraian</th>
                <th>Nama</th>
                <th class="center" style="width: 62px;">Diterima</th>
                <th class="center" style="width: 62px;">Selesai diperiksa</th>
                <th class="center" style="width: 70px;">Tanda tangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($progres as $index => $row)
                <tr>
                    <td class="no">{{ $index + 1 }}</td>
                    <td>{{ $row['uraian'] }}</td>
                    <td>
                        @foreach ($row['nama'] as $nama)
                            {{ $loop->iteration }}. {{ $nama }}@if (! $loop->last)<br>@endif
                        @endforeach
                    </td>
                    <td class="center">{{ $row['diterima']?->format('d/m/Y') }}</td>
                    <td class="center">{{ $row['selesai']?->format('d/m/Y') }}</td>
                    <td class="ttd"></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>MANUAL PEMERIKSAAN MANDIRI PUBLIKASI (KF/BPS Kab/Kota)</h3>
    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Uraian</th>
                <th class="check">Check List<br>(Ketua Tim)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sections as $section)
                <tr class="section-row"><td colspan="3">{{ $section->name }}</td></tr>
                @php($lastGroup = null)
                @foreach ($section->items as $item)
                    @if ($item->group_label && $item->group_label !== $lastGroup)
                        <tr class="group-row"><td colspan="3">{{ $item->group_label }}</td></tr>
                        @php($lastGroup = $item->group_label)
                    @endif
                    @php($answer = $answersByItem->get($item->id))
                    <tr>
                        <td class="no">{{ $item->number }}</td>
                        <td>
                            {!! nl2br(e($item->content)) !!}
                        </td>
                        <td class="check">
                            @if ($answer?->status === \App\Enums\ChecklistStatus::Sesuai)
                                ✓
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <p class="legend">Keterangan: ✓ = Telah diperiksa dan sesuai</p>

    <p class="catatan">
        Kartu kendali ini diisi dan disetujui oleh Ketua Tim {{ $card->ketuaTim->name }}
        pada {{ $card->approved_at->translatedFormat('d F Y H:i') }} WIB melalui aplikasi Grobook.<br>
        Jadwal Publikasi ARC dan Non ARC BPS Provinsi Sumatera Selatan: http://s.bps.go.id/daftarARC2024<br>
        Pedoman Publikasi: http://s.bps.go.id/PedomanPublikasi16
    </p>
</body>
</html>