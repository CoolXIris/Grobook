@extends('layouts.app')

@section('title', $team->exists ? 'Edit Tim' : 'Daftarkan Tim')

@section('content')
    <div class="mx-auto {{ $team->exists ? 'max-w-6xl' : 'max-w-3xl' }} rounded-xl border border-slate-200 bg-white p-6 shadow-2xs">
        <form action="{{ $team->exists ? route('teams.update', $team) : route('teams.store') }}" method="POST" class="space-y-6">
            @csrf
            @if ($team->exists)
                @method('PUT')
                <input type="hidden" name="sync_arc" value="1">
                <input type="hidden" name="sync_members" value="1">
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <x-field name="code" label="Kode Tim (Otomatis)" required hint="Dibuat otomatis, tetap dapat diubah oleh Petugas DLS.">
                    <input type="text" id="code" name="code" value="{{ old('code', $team->code) }}" maxlength="50" class="w-full rounded-lg border border-slate-300 bg-slate-50/60 px-3 py-2 font-mono text-sm font-semibold uppercase focus:bg-white" required>
                </x-field>
                <div class="sm:col-span-2">
                    <x-field name="name" label="Nama Tim" required hint="Mis. Tim Penyusun Naskah Sosial.">
                        <input type="text" id="name" name="name" value="{{ old('name', $team->name) }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </x-field>
                </div>
            </div>

            @if (! $team->exists)
                <x-field name="ketua_tim_email" label="Akun Google Ketua Tim (email)" required hint="Jika akun belum ada, sistem akan mendaftarkannya dengan role Ketua Tim. Akun aktif saat yang bersangkutan login dengan Google.">
                    <input type="email" id="ketua_tim_email" name="ketua_tim_email" value="{{ old('ketua_tim_email', $ketuaTimEmail) }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </x-field>
            @endif

            {{-- Dropdown Multilist Assign Judul Publikasi ARC (di atas tabel) --}}
            @php($selectedArcIds = array_map('intval', old('arc_publication_ids', $team->exists ? $team->arcPublications->pluck('id')->all() : [])))
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Assign Judul Publikasi ARC ke Tim Ini</label>
                <p class="mb-2 text-xs text-slate-500">Pilih satu atau beberapa judul publikasi ARC melalui dropdown multilist di bawah. Judul yang sudah di-assign ke tim lain terkunci otomatis.</p>

                <div class="relative" id="arc-multilist-container">
                    <button type="button"
                            id="arc-multilist-btn"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            class="flex w-full items-center justify-between rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-left text-sm text-slate-700 shadow-2xs transition hover:border-slate-400">
                        <span id="arc-multilist-summary" class="truncate font-medium">Pilih judul publikasi ARC...</span>
                        <svg id="arc-multilist-chevron" class="size-4 shrink-0 text-slate-500 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="arc-multilist-dropdown"
                         class="absolute z-30 mt-1.5 hidden w-full rounded-xl border border-slate-200 bg-white shadow-lg">
                        <div class="flex items-center gap-2 border-b border-slate-100 p-2.5">
                            <input type="text"
                                   id="arc-multilist-search"
                                   placeholder="Cari judul publikasi ARC..."
                                   autocomplete="off"
                                   class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs">
                            <button type="button" id="arc-select-all" class="shrink-0 rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Pilih Semua</button>
                            <button type="button" id="arc-clear-all" class="shrink-0 rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Reset</button>
                        </div>
                        <div class="max-h-64 divide-y divide-slate-100 overflow-y-auto" role="listbox" aria-multiselectable="true">
                            @forelse ($arcPublications as $arc)
                                @php($assignedToOther = $arc->team_id !== null && $arc->team_id !== $team->id)
                                <label data-title="{{ mb_strtolower($arc->title.' '.$arc->title_en) }}"
                                       data-label="{{ $arc->title }}"
                                       class="arc-multilist-item flex items-start gap-3 px-3.5 py-2.5 text-sm {{ $assignedToOther ? 'cursor-not-allowed bg-slate-100/80 opacity-60' : 'cursor-pointer hover:bg-blue-50/50' }}">
                                    <input type="checkbox"
                                           name="arc_publication_ids[]"
                                           value="{{ $arc->id }}"
                                           data-arc-title="{{ $arc->title }}"
                                           class="arc-checkbox mt-0.5 size-4 rounded border-slate-300"
                                           @checked(! $assignedToOther && in_array($arc->id, $selectedArcIds, true))
                                           @disabled($assignedToOther)>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-slate-800">{{ $arc->title }}</p>
                                        <p class="text-xs text-slate-500">{{ $arc->title_en }}</p>
                                        @if ($assignedToOther)
                                            <span class="mt-1 inline-block rounded bg-slate-200 px-1.5 py-0.5 text-[11px] font-semibold text-slate-600">Sudah di-assign ke {{ $arc->team?->name }}</span>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <p class="px-4 py-4 text-center text-xs text-slate-500">Belum ada master judul publikasi ARC.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div id="arc-selected-chips" class="mt-2 flex flex-wrap gap-1.5"></div>

                @error('arc_publication_ids')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('arc_publication_ids.*')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tabel Anggota & Ketua Tim (pada halaman Edit Tim) --}}
            @if ($team->exists)
                @php($oldMembers = old('members'))
                @php($rows = $oldMembers !== null
                    ? collect($oldMembers)
                    : $teamMembers->map(fn ($m) => [
                        'id' => $m->id,
                        'email' => $m->email,
                        'name' => $m->name,
                        'role' => $m->role->value,
                        'nip' => $m->nip,
                        'jabatan' => $m->jabatan,
                        'phone' => $m->phone,
                    ]))

                <div class="space-y-3 border-t border-slate-200 pt-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 id="team-members-caption" class="text-sm font-semibold text-slate-800">Daftar Ketua Tim &amp; Anggota Tim Pembuat Naskah</h3>
                            <p class="text-xs text-slate-500">Seluruh data (Email, Nama, Role, NIP, Jabatan, No. Telepon) dapat diedit langsung oleh Petugas DLS. Pastikan terdapat tepat 1 akun dengan role Ketua Tim.</p>
                        </div>
                        <button type="button"
                                id="add-member-row-btn"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-800 transition hover:bg-blue-100">
                            <span>+ Tambah Anggota</span>
                        </button>
                    </div>

                    @error('members')
                        <div class="rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs font-medium text-red-700">{{ $message }}</div>
                    @enderror

                    <div tabindex="0" role="region" aria-labelledby="team-members-caption" class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-3 py-2.5 min-w-52">Email (Akun Google) *</th>
                                    <th class="px-3 py-2.5 min-w-44">Nama *</th>
                                    <th class="px-3 py-2.5 min-w-44">Role *</th>
                                    <th class="px-3 py-2.5 min-w-36">NIP</th>
                                    <th class="px-3 py-2.5 min-w-40">Jabatan</th>
                                    <th class="px-3 py-2.5 min-w-36">No. Telepon</th>
                                    <th class="px-3 py-2.5 w-20 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="team-members-tbody" class="divide-y divide-slate-100 bg-white">
                                @foreach ($rows as $idx => $member)
                                    <tr class="member-row hover:bg-slate-50/70">
                                        <td class="px-3 py-2">
                                            <input type="hidden" name="members[{{ $idx }}][id]" value="{{ $member['id'] ?? '' }}">
                                            <input type="email"
                                                   name="members[{{ $idx }}][email]"
                                                   value="{{ $member['email'] ?? '' }}"
                                                   maxlength="255"
                                                   required
                                                   placeholder="nama@gmail.com"
                                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="members[{{ $idx }}][name]"
                                                   value="{{ $member['name'] ?? '' }}"
                                                   maxlength="255"
                                                   required
                                                   placeholder="Nama lengkap"
                                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select name="members[{{ $idx }}][role]"
                                                    class="member-role-select w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium">
                                                <option value="ketua_tim" @selected(($member['role'] ?? '') === 'ketua_tim')>Ketua Tim</option>
                                                <option value="tim_naskah" @selected(($member['role'] ?? 'tim_naskah') === 'tim_naskah')>Tim Pembuat Naskah</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="members[{{ $idx }}][nip]"
                                                   value="{{ $member['nip'] ?? '' }}"
                                                   maxlength="30"
                                                   placeholder="NIP"
                                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="members[{{ $idx }}][jabatan]"
                                                   value="{{ $member['jabatan'] ?? '' }}"
                                                   maxlength="255"
                                                   placeholder="Jabatan"
                                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text"
                                                   name="members[{{ $idx }}][phone]"
                                                   value="{{ $member['phone'] ?? '' }}"
                                                   maxlength="30"
                                                   placeholder="No. Telepon"
                                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button"
                                                    class="remove-member-row-btn rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-3 border-t border-slate-100 pt-4">
                <button type="submit" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ $team->exists ? 'Simpan Perubahan' : 'Daftarkan Tim' }}</button>
                <a href="{{ route('teams.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>

    <script>
        (() => {
            // Dropdown Multilist Assign Judul Publikasi ARC
            const container = document.getElementById('arc-multilist-container');
            const btn = document.getElementById('arc-multilist-btn');
            const dropdown = document.getElementById('arc-multilist-dropdown');
            const chevron = document.getElementById('arc-multilist-chevron');
            const summary = document.getElementById('arc-multilist-summary');
            const chipsContainer = document.getElementById('arc-selected-chips');
            const searchInput = document.getElementById('arc-multilist-search');
            const checkboxes = Array.from(document.querySelectorAll('.arc-checkbox'));
            const items = Array.from(document.querySelectorAll('.arc-multilist-item'));

            const updateArcSummary = () => {
                if (!summary || !chipsContainer) return;
                const checked = checkboxes.filter(cb => cb.checked && !cb.disabled);
                if (checked.length === 0) {
                    summary.textContent = '— Pilih judul publikasi ARC —';
                    summary.classList.add('text-slate-500');
                } else {
                    summary.textContent = `${checked.length} judul publikasi ARC dipilih`;
                    summary.classList.remove('text-slate-500');
                }

                chipsContainer.innerHTML = '';
                checked.forEach(cb => {
                    const chip = document.createElement('span');
                    chip.className = 'inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-800 ring-1 ring-inset ring-blue-600/20';
                    const text = document.createElement('span');
                    text.textContent = cb.dataset.arcTitle || '';
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'text-blue-500 hover:text-blue-900 font-bold';
                    removeBtn.textContent = '×';
                    removeBtn.setAttribute('aria-label', 'Hapus pilihan');
                    removeBtn.addEventListener('click', () => {
                        cb.checked = false;
                        updateArcSummary();
                    });
                    chip.appendChild(text);
                    chip.appendChild(removeBtn);
                    chipsContainer.appendChild(chip);
                });
            };

            btn?.addEventListener('click', () => {
                const isHidden = dropdown.classList.toggle('hidden');
                btn.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                chevron?.classList.toggle('rotate-180', !isHidden);
                if (!isHidden) {
                    searchInput?.focus();
                }
            });

            document.addEventListener('click', (e) => {
                if (container && !container.contains(e.target)) {
                    dropdown?.classList.add('hidden');
                    btn?.setAttribute('aria-expanded', 'false');
                    chevron?.classList.remove('rotate-180');
                }
            });

            searchInput?.addEventListener('input', () => {
                const q = searchInput.value.trim().toLowerCase();
                items.forEach(item => {
                    const hay = item.dataset.title || '';
                    item.classList.toggle('hidden', q.length > 0 && !hay.includes(q));
                });
            });

            document.getElementById('arc-select-all')?.addEventListener('click', () => {
                checkboxes.forEach(cb => {
                    if (!cb.disabled && !cb.closest('.arc-multilist-item')?.classList.contains('hidden')) {
                        cb.checked = true;
                    }
                });
                updateArcSummary();
            });

            document.getElementById('arc-clear-all')?.addEventListener('click', () => {
                checkboxes.forEach(cb => {
                    if (!cb.disabled) cb.checked = false;
                });
                updateArcSummary();
            });

            checkboxes.forEach(cb => cb.addEventListener('change', updateArcSummary));
            updateArcSummary();

            // Tabel Anggota Tim Dinamis (pada halaman Edit Tim)
            const tbody = document.getElementById('team-members-tbody');
            const addRowBtn = document.getElementById('add-member-row-btn');
            let rowCounter = tbody ? tbody.querySelectorAll('.member-row').length : 0;

            const bindRowEvents = (row) => {
                row.querySelector('.remove-member-row-btn')?.addEventListener('click', () => {
                    row.remove();
                });

                // Bila sebuah baris diubah menjadi Ketua Tim, otomatis ubah baris lain menjadi Tim Pembuat Naskah
                const roleSelect = row.querySelector('.member-role-select');
                roleSelect?.addEventListener('change', () => {
                    if (roleSelect.value === 'ketua_tim' && tbody) {
                        tbody.querySelectorAll('.member-role-select').forEach(other => {
                            if (other !== roleSelect && other.value === 'ketua_tim') {
                                other.value = 'tim_naskah';
                            }
                        });
                    }
                });
            };

            tbody?.querySelectorAll('.member-row').forEach(bindRowEvents);

            addRowBtn?.addEventListener('click', () => {
                if (!tbody) return;
                const idx = rowCounter++;
                const tr = document.createElement('tr');
                tr.className = 'member-row hover:bg-slate-50/70';
                tr.innerHTML = `
                    <td class="px-3 py-2">
                        <input type="hidden" name="members[${idx}][id]" value="">
                        <input type="email" name="members[${idx}][email]" maxlength="255" required placeholder="nama@gmail.com" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="members[${idx}][name]" maxlength="255" required placeholder="Nama lengkap" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                    </td>
                    <td class="px-3 py-2">
                        <select name="members[${idx}][role]" class="member-role-select w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium">
                            <option value="ketua_tim">Ketua Tim</option>
                            <option value="tim_naskah" selected>Tim Pembuat Naskah</option>
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="members[${idx}][nip]" maxlength="30" placeholder="NIP" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="members[${idx}][jabatan]" maxlength="255" placeholder="Jabatan" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="members[${idx}][phone]" maxlength="30" placeholder="No. Telepon" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                    </td>
                    <td class="px-3 py-2 text-right">
                        <button type="button" class="remove-member-row-btn rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                    </td>
                `;
                tbody.appendChild(tr);
                bindRowEvents(tr);
                tr.querySelector('input[type="email"]')?.focus();
            });
        })();
    </script>
@endsection
