<?php

namespace App\Http\Controllers;

use App\Models\ChecklistSection;
use App\Models\QualityCardAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChecklistSectionController extends Controller
{
    public function create(): View
    {
        return view('checklist.section-form', [
            'section' => new ChecklistSection([
                'position' => (int) ChecklistSection::max('position') + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ChecklistSection::create($this->validated($request));

        return redirect()->route('checklist-items.index')->with('success', 'Bagian kartu kendali berhasil ditambahkan.');
    }

    public function edit(ChecklistSection $checklistSection): View
    {
        return view('checklist.section-form', [
            'section' => $checklistSection,
        ]);
    }

    public function update(Request $request, ChecklistSection $checklistSection): RedirectResponse
    {
        $checklistSection->update($this->validated($request));

        return redirect()->route('checklist-items.index')->with('success', 'Bagian kartu kendali berhasil diperbarui.');
    }

    public function destroy(ChecklistSection $checklistSection): RedirectResponse
    {
        DB::transaction(function () use ($checklistSection) {
            $itemIds = $checklistSection->items()->pluck('id');
            if ($itemIds->isNotEmpty()) {
                QualityCardAnswer::whereIn('checklist_item_id', $itemIds)->delete();
                $checklistSection->items()->delete();
            }

            $checklistSection->delete();
        });

        return redirect()->route('checklist-items.index')->with('success', 'Bagian kartu kendali berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'position' => ['required', 'integer', 'min:1', 'max:999'],
            'name' => ['required', 'string', 'max:255'],
        ], [], [
            'position' => 'nomor urut bagian',
            'name' => 'judul bagian kartu kendali',
        ]);
    }
}
