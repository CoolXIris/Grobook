@extends('layouts.app')

@section('title', 'Ajukan Publikasi')

@section('content')
    <div class="mx-auto max-w-3xl">
        <p class="mb-6 text-sm text-slate-600">Pilih tipe publikasi yang akan diajukan. Publikasi <strong>ARC</strong> (Agenda Rilis Kegiatan) judulnya sudah ditentukan oleh BPS, sedangkan <strong>NON ARC</strong> di luar agenda rilis rutin.</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('publications.create-form', 'arc') }}" class="group rounded-xl border-2 border-slate-200 bg-white p-6 transition hover:border-blue-500 hover:shadow-md">
                <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">ARC</span>
                <h2 class="mt-3 text-lg font-semibold group-hover:text-blue-800">Publikasi ARC</h2>
                <p class="mt-2 text-sm text-slate-500">Judul dan judul Inggris dipilih dari daftar publikasi ARC yang sudah ditentukan. Isi frekuensi terbit, bahasa, dan jadwal rilis.</p>
            </a>
            <a href="{{ route('publications.create-form', 'non_arc') }}" class="group rounded-xl border-2 border-slate-200 bg-white p-6 transition hover:border-blue-500 hover:shadow-md">
                <span class="inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">NON ARC</span>
                <h2 class="mt-3 text-lg font-semibold group-hover:text-blue-800">Publikasi NON ARC</h2>
                <p class="mt-2 text-sm text-slate-500">Isi metadata lengkap: tahun terbit, judul, estimasi rilis, jumlah halaman, kerjasama, pembuat cover, orientasi, dan ukuran kertas.</p>
            </a>
        </div>
    </div>
@endsection