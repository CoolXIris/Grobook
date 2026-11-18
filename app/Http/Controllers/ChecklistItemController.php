<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\ChecklistSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChecklistItemController extends Controller
{
    public function index(): View
    {
        $sections = ChecklistSection::orderBy('position')
            ->with(['items'])
            ->get();

        return view('checklist.index', ['sections' => $sections]);
    }

    public function create(): View
    {
        return view('checklist.form', [
            'item' => new ChecklistItem(['number' => (int) ChecklistItem::max('number') + 1, 'is_active' => true]),
            'sections' => ChecklistSection::orderBy('position')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        ChecklistItem::create($data);

        return redirect()->route('checklist-items.index')->with('success', 'Butir pemeriksaan berhasil ditambahkan.');
    }

    public function edit(ChecklistItem $checklistItem): View
    {
        return view('checklist.form', [
            'item' => $checklistItem,
            'sections' => ChecklistSection::orderBy('position')->get(),
        ]);
    }

    public function update(Request $request, ChecklistItem $checklistItem): RedirectResponse
    {
        $checklistItem->update($this->validated($request));

        return redirect()->route('checklist-items.index')->with('success', 'Butir pemeriksaan berhasil diperbarui.');
    }

    public function destroy(ChecklistItem $checklistItem): RedirectResponse
    {
        if ($checklistItem->answers()->exists()) {
            return redirect()->route('checklist-items.index')->with('error', 'Butir sudah pernah dipakai pada kartu kendali sehingga tidak dapat dihapus. Nonaktifkan saja agar tidak muncul pada kartu kendali baru.');
        }

        $checklistItem->delete();

        return redirect()->route('checklist-items.index')->with('success', 'Butir pemeriksaan berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'checklist_section_id' => ['required', 'exists:checklist_sections,id'],
            'number' => ['required', 'integer', 'min:1', 'max:999'],
            'group_label' => ['nullable', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:5000'],
            'position' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
