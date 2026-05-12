<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:1,2,3'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'created_at' => ['nullable', 'date'],
        ];
    }

        public function messages(): array
    {
        return [
            'gender.in' => '性別の値が不正です',
            'category_id.exists' => '選択されたカテゴリーが存在しません',
        ];
    }
}