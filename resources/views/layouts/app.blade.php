<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Grobook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-[#032860] bg-[#043277] text-white lg:flex">
            <div class="flex items-center gap-3.5 border-b border-[#032860] px-5 py-4">
                <div class="flex items-center gap-2.5 shrink-0">
                    <img src="{{ asset('img/pst_transparent.png') }}" alt="Logo PST" class="h-9 w-auto object-contain filter drop-shadow-sm">
                    <div class="h-6 w-px bg-white/20"></div>
                    <img src="{{ asset('img/logo_bps.svg') }}" alt="Logo BPS" class="size-8 object-contain">
                </div>
                <div class="min-w-0">
                    <p class="text-base font-bold tracking-tight text-white leading-tight">Grobook</p>
                    <p class="mt-0.5 text-[11px] leading-tight text-blue-200/80">
                        Publikasi BPS Prov.<br>
                        <span class="text-blue-100 font-medium">Sumatera Selatan</span>
                    </p>
                </div>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
                @php($nav = [
                    ['route' => 'dashboard', 'label' => 'Dashboard', 'roles' => null, 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'publications.create', 'label' => 'Ajukan Publikasi', 'roles' => ['tim_naskah'], 'icon' => 'M12 4v16m8-8H4'],
                    ['route' => 'reviews.content.index', 'label' => 'Review Konten', 'roles' => ['ketua_tim'], 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'team-members.index', 'label' => 'Anggota Tim', 'roles' => ['ketua_tim'], 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['route' => 'reviews.dls.index', 'label' => 'Review Layout & Non-Konten', 'roles' => ['petugas_dls'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    ['route' => 'documents.index', 'label' => 'Unduh Dokumen', 'roles' => ['petugas_dls'], 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'],
                    ['route' => 'checklist-items.index', 'label' => 'Butir Kartu Kendali', 'roles' => ['petugas_dls'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['route' => 'arc-publications.index', 'label' => 'Daftar Publikasi ARC', 'roles' => ['petugas_dls'], 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['route' => 'teams.index', 'label' => 'Ketua Tim & Tim', 'roles' => ['petugas_dls'], 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    ['route' => 'reviews.final.index', 'label' => 'Pengecekan Akhir', 'roles' => ['ketua_dls'], 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    ['route' => 'users.index', 'label' => 'Kelola User', 'roles' => ['ketua_dls'], 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['route' => 'publications.index', 'label' => 'Daftar Publikasi', 'roles' => null, 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    ['route' => 'history.index', 'label' => 'Histori Publikasi', 'roles' => null, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ])
                @foreach ($nav as $item)
                    @continue($item['roles'] !== null && ! in_array(auth()->user()->role->value, $item['roles'], true))
                    @php($active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'*'))
                    <a href="{{ route($item['route']) }}"
                       class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ $active ? 'bg-white/15 font-semibold text-white shadow-2xs ring-1 ring-inset ring-white/20' : 'text-blue-100/75 hover:bg-white/10 hover:text-white' }}">
                        <svg class="size-4 shrink-0 {{ $active ? 'text-white' : 'text-blue-200/70 group-hover:text-white' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-[#032860] px-5 py-3.5 text-[11px] leading-relaxed text-blue-200/75">
                Proses pengajuan publikasi internal<br>
                <span class="font-medium text-white">ARC &amp; NON ARC</span>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" id="sidebar-toggle" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('img/pst_transparent.png') }}" alt="Logo PST" class="h-7 w-auto object-contain lg:hidden">
                            <div class="h-4 w-px bg-slate-200 lg:hidden"></div>
                            <img src="{{ asset('img/logo_bps.svg') }}" alt="Logo BPS" class="size-6 object-contain lg:hidden">
                            <div>
                                <p class="text-sm font-bold text-slate-900 lg:hidden">Grobook</p>
                                <h1 class="hidden text-base font-semibold tracking-tight text-slate-900 lg:block">@yield('title', 'Dashboard')</h1>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                            @php($availableRoles = auth()->user()->assignedRoles())
                            @if (count($availableRoles) > 1)
                                <form method="POST" action="{{ route('role.switch') }}" class="mt-0.5 inline-block">
                                    @csrf
                                    <select name="role"
                                            aria-label="Ganti Role Aktif"
                                            onchange="this.form.submit()"
                                            class="cursor-pointer rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700 transition hover:border-blue-300 hover:bg-blue-50/50 focus:border-blue-500 focus:outline-none">
                                        @foreach ($availableRoles as $roleOption)
                                            <option value="{{ $roleOption->value }}" @selected(auth()->user()->role === $roleOption)>
                                                {{ $roleOption->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <p class="text-xs text-slate-500">{{ auth()->user()->role->label() }}</p>
                            @endif
                        </div>
                        @if (auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="" class="size-8 rounded-full ring-1 ring-slate-200" referrerpolicy="no-referrer">
                        @else
                            <div class="flex size-8 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-600/20">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-2xs transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
                <div id="mobile-nav" class="hidden border-t border-slate-200/80 bg-white px-4 py-2 lg:hidden">
                    <nav class="grid gap-1 text-sm">
                        @foreach ($nav as $item)
                            @continue($item['roles'] !== null && ! in_array(auth()->user()->role->value, $item['roles'], true))
                            <a href="{{ route($item['route']) }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 {{ request()->routeIs($item['route']) ? 'bg-blue-50 font-semibold text-blue-800' : 'text-slate-600 hover:bg-slate-50' }}">
                                <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                                </svg>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8">
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