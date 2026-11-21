<?php

namespace App\Http\Requests;

use App\Enums\CoverMaker;
use App\Enums\Frequency;
use App\Enums\Language;
use App\Enums\Orientation;
use App\Enums\PublicationType;
use App\Enums\PublishedFor;
use App\Support\RomanNumeral;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('publication'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('release_date')) {
            $date = \DateTimeImmutable::createFromFormat('m/d/Y', $this->string('release_date')->toString());

            $this->merge([
                'release_date' => $date !== false ? $date->format('Y-m-d') : $this->input('release_date'),
            ]);
        }

        if ($this->filled('pages_romawi') && ! is_numeric($this->input('pages_romawi'))) {
            $parsedRoman = RomanNumeral::toEvenInt($this->input('pages_romawi'));
            if ($parsedRoman !== null) {
                $this->merge(['pages_romawi' => $parsedRoman]);
            }
        }

        if ($this->filled('paper_size')) {
            $rawSize = $this->string('paper_size')->toString();
            $flipped = array_flip(StorePublicationRequest::PAPER_SIZES);
            if (isset($flipped[$rawSize])) {
                $this->merge(['paper_size' => $flipped[$rawSize]]);
            }
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isNonArc = $this->route('publication')->type === PublicationType::NonArc;

        return [
            'arc_publication_id' => [
                Rule::requiredIf(! $isNonArc),
                'nullable',
                Rule::exists('arc_publications', 'id')
                    ->where('is_active', true)
                    ->where('team_id', $this->user()->team_id),
            ],
            'title' => [Rule::requiredIf($isNonArc), 'nullable', 'string', 'max:500'],
            'title_en' => ['nullable', 'string', 'max:500'],
            'release_date' => ['required', 'date'],
            'frequency' => ['required', Rule::enum(Frequency::class)],
            'language' => ['required', Rule::enum(Language::class)],
            'year' => [Rule::requiredIf($isNonArc), 'nullable', 'integer', 'min:1900', 'max:2100'],
            'pages_romawi' => [
                Rule::requiredIf($isNonArc),
                'nullable',
                'integer',
                'min:2',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && ((int) $value % 2 !== 0)) {
                        $fail('Jumlah halaman romawi harus bernilai genap antara II (2) sampai C (100).');
                    }
                },
            ],
            'pages_arabic' => [Rule::requiredIf($isNonArc), 'nullable', 'integer', 'min:1', 'max:5000'],
            'has_cooperation' => [Rule::requiredIf($isNonArc), 'nullable', 'boolean'],
            'published_for' => [Rule::requiredIf($isNonArc), 'nullable', Rule::enum(PublishedFor::class)],
            'cover_maker' => [Rule::requiredIf($isNonArc), 'nullable', Rule::enum(CoverMaker::class)],
            'orientation' => [Rule::requiredIf($isNonArc), 'nullable', Rule::enum(Orientation::class)],
            'paper_size' => [Rule::requiredIf($isNonArc), 'nullable', 'string', Rule::in(array_keys(StorePublicationRequest::PAPER_SIZES))],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:30720'],
            'drive_link' => ['required', 'url', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return (new StorePublicationRequest)->attributes();
    }
}