@extends('layouts.app')

@section('title', 'Review Layout & Non-Konten')

@section('content')
    <p class="mb-4 text-sm text-slate-600">Naskah yang sudah disetujui ketua tim dan menunggu pemeriksaan layout & non-konten. Buka detail untuk meneruskan ke Ketua DLS atau mengajukan revisi.</p>
    <x-publication-table :publications="$publications" empty-text="Tidak ada naskah yang menunggu review layout." />
@endsection