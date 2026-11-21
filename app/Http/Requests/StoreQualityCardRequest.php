<?php

namespace App\Http\Requests;

use App\Enums\ChecklistStatus;
use App\Models\ChecklistItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQualityCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reviewContent', $this->route('publication'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*.status' => ['required', Rule::in([ChecklistStatus::Sesuai->value])],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $activeItemIds = ChecklistItem::active()->pluck('id')->all();
                $answers = $this->input('answers', []);
                $answeredIds = is_array($answers) ? array_map('intval', array_keys($answers)) : [];

                if (array_diff($activeItemIds, $answeredIds) !== []) {
                    $validator->errors()->add('answers', 'Semua butir pemeriksaan wajib dicentang sebelum menyetujui publikasi.');

                    return;
                }

                if (array_diff($answeredIds, $activeItemIds) !== []) {
                    $validator->errors()->add('answers', 'Terdapat butir pemeriksaan yang tidak valid.');

                    return;
                }

                foreach ($answers as $answer) {
                    if (($answer['status'] ?? null) !== ChecklistStatus::Sesuai->value) {
                        $validator->errors()->add('answers', 'Semua butir pemeriksaan wajib dicentang sebelum menyetujui publikasi.');

                        return;
                    }
                }
            },
        ];
    }
}