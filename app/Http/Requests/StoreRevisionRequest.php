<?php

namespace App\Http\Requests;

use App\Enums\PublicationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('requestRevision', $this->route('publication'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isDls = $this->route('publication')->status === PublicationStatus::ReviewDls;

        return [
            'comment' => ['required', 'string', 'max:5000'],
            'file' => [Rule::requiredIf($isDls), 'nullable', 'file', 'mimes:pdf', 'max:51200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'comment' => 'komentar/catatan revisi',
            'file' => 'file detail revisi (PDF)',
        ];
    }
}