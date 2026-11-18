@extends('layouts.app')

@section('title', 'Review Konten')

@section('content')
    <p class="mb-4 text-sm text-slate-600">Naskah dari anggota tim Anda yang menunggu pemeriksaan konten. Buka detail untuk memeriksa naskah, mengisi kartu kendali, dan menyetujui atau meminta revisi.</p>
    <x-publication-table :publications="$publications" empty-text="Tidak ada naskah yang menunggu pemeriksaan konten." />
@endsection