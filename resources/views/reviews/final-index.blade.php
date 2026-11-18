@extends('layouts.app')

@section('title', 'Pengecekan Akhir')

@section('content')
    <p class="mb-4 text-sm text-slate-600">Naskah yang sudah lolos review Petugas DLS dan menunggu pengecekan akhir. Buka detail untuk melihat naskah dan timeline proses, lalu setujui atau minta revisi.</p>
    <x-publication-table :publications="$publications" empty-text="Tidak ada naskah yang menunggu pengecekan akhir." />
@endsection