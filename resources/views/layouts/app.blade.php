<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Grobook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-blue-950 text-white lg:flex">
            <div class="border-b border-blue-900 px-6 py-5">
                <p class="text-xl font-bold tracking-wide">Grobook</p>
                <p class="mt-1 text-xs text-blue-300">Publikasi BPS Prov. Sumatera Selatan</p>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
                @php($nav = [
                    ['route' => 'dashboard', 'label' => 'Dashboard', 'roles' => null],
                    ['route' => 'publications.create', 'label' => 'Ajukan Publikasi', 'roles' => ['tim_naskah']],
                    ['route' => 'reviews.content.index', 'label' => 'Review Konten', 'roles' => ['ketua_tim']],
                    ['route' => 'team-members.index', 'label' => 'Anggota Tim', 'roles' => ['ketua_tim']],
                    ['route' => 'reviews.dls.index', 'label' => 'Review Layout & Non-Konten', 'roles' => ['petugas_dls']],
                    ['route' => 'documents.index', 'label' => 'Unduh Dokumen', 'roles' => ['petugas_dls']],
                    ['route' => 'checklist-items.index', 'label' => 'Butir Kartu Kendali', 'roles' => ['petugas_dls']],
                    ['route' => 'arc-publications.index', 'label' => 'Daftar Publikasi ARC', 'roles' => ['petugas_dls']],
                    ['route' => 'teams.index', 'label' => 'Ketua Tim & Tim', 'roles' => ['petugas_dls']],
                    ['route' => 'reviews.final.index', 'label' => 'Pengecekan Akhir', 'roles' => ['ketua_dls']],
                    ['route' => 'users.index', 'label' => 'Kelola User', 'roles' => ['ketua_dls']],
                    ['route' => 'publications.index', 'label' => 'Daftar Publikasi', 'roles' => null],
                    ['route' => 'history.index', 'label' => 'Histori Publikasi', 'roles' => null],
                ])
                @foreach ($nav as $item)
                    @continue($item['roles'] !== null && ! in_array(auth()->user()->role->value, $item['roles'], true))
                    <a href="{{ route($item['route']) }}"
                       class="block rounded-lg px-3 py-2 transition {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'*') ? 'bg-blue-800 font-semibold text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-blue-900 px-6 py-4 text-xs text-blue-300">
                Proses pengajuan publikasi internal<br>ARC & NON ARC
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white">
                <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button type="button" id="sidebar-toggle" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div>
                            <p class="text-sm font-semibold lg:hidden">Grobook</p>
                            <h1 class="hidden text-lg font-semibold lg:block">@yield('title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->role->label() }}</p>
                        </div>
                        @if (auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="" class="size-9 rounded-full" referrerpolicy="no-referrer">
                        @else
                            <div class="flex size-9 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-800">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Keluar</button>
                        </form>
                    </div>
                </div>
                <div id="mobile-nav" class="hidden border-t border-slate-200 px-4 py-2 lg:hidden">
                    <nav class="grid gap-1 text-sm">
                        @foreach ($nav as $item)
                            @continue($item['roles'] !== null && ! in_array(auth()->user()->role->value, $item['roles'], true))
                            <a href="{{ route($item['route']) }}" class="rounded px-2 py-1.5 {{ request()->routeIs($item['route']) ? 'bg-blue-50 font-semibold text-blue-800' : 'text-slate-600 hover:bg-slate-100' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </nav>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6">
                <x-flash />
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
            document.getElementById('mobile-nav')?.classList.toggle('hidden');
        });
    </script>
</body>
</html>