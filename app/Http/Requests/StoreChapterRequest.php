<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Novel;

class StoreChapterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 小説が存在するかチェック
        $novel = Novel::find($this->route('id'));
        return $novel !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'chapter_number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:50000',
        ];
    }

    public function messages(): array
    {
        return [
            'chapter_number.required' => '章番号は必須です',
            'chapter_number.min' => '章番号は1以上で入力してください',
            'title.required' => '章タイトルは必須です',
            'content.required' => '本文は必須です',
            'content.max' => '本文は50000文字以内で入力してください',
        ];
    }
}
