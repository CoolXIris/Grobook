@extends('layouts.app')

@section('title', 'Ajukan Publikasi')

@section('content')
    <div class="mx-auto max-w-3xl">
        <p class="mb-6 text-sm leading-relaxed text-slate-600">Pilih tipe publikasi yang akan diajukan. Publikasi <strong class="font-semibold text-slate-800">ARC</strong> (Agenda Rilis Kegiatan) judulnya sudah ditentukan oleh BPS, sedangkan <strong class="font-semibold text-slate-800">NON ARC</strong> di luar agenda rilis rutin.</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('publications.create-form', 'arc') }}" class="group flex flex-col justify-between rounded-xl border border-slate-200/80 bg-white p-6 shadow-2xs transition hover:border-blue-400 hover:shadow-sm">
                <div>
                    <span class="inline-flex rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/20">ARC</span>
                    <h2 class="mt-3.5 text-base font-semibold text-slate-900 group-hover:text-blue-700">Publikasi ARC</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Judul dan judul Inggris dipilih dari daftar publikasi ARC yang sudah ditentukan. Isi frekuensi terbit, bahasa, dan jadwal rilis.</p>
                </div>
                <p class="mt-4 text-xs font-semibold text-blue-700">Pilih ARC &rarr;</p>
            </a>
            <a href="{{ route('publications.create-form', 'non_arc') }}" class="group flex flex-col justify-between rounded-xl border border-slate-200/80 bg-white p-6 shadow-2xs transition hover:border-blue-400 hover:shadow-sm">
                <div>
                    <span class="inline-flex rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-500/20">NON ARC</span>
                    <h2 class="mt-3.5 text-base font-semibold text-slate-900 group-hover:text-blue-700">Publikasi NON ARC</h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Isi metadata lengkap: tahun terbit, judul, estimasi rilis, jumlah halaman, kerjasama, pembuat cover, orientasi, dan ukuran kertas.</p>
                </div>
                <p class="mt-4 text-xs font-semibold text-blue-700">Pilih NON ARC &rarr;</p>
            </a>
        </div>
    </div>
@endsection