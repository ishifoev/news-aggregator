<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'category' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
        ];
    }
}
