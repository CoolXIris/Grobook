@extends('layouts.app')

@section('title', 'Histori Publikasi')

@section('content')
    <p class="mb-4 text-sm text-slate-600">Publikasi yang telah selesai (disetujui Ketua DLS) sesuai cakupan peran Anda. Klik Detail untuk melihat metadata, versi file, kartu kendali, dan timeline lengkap.</p>
    <x-publication-table :publications="$publications" empty-text="Belum ada publikasi yang selesai." />
@endsection