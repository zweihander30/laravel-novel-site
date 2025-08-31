<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNovelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 今は認証なしなので常にtrue
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'author_name'=> 'nullable|string|max:100',
        ];
    }

    /**
     * カスタムエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'title.required'=> 'タイトルは必須です',
            'title.max'=> 'タイトルは255文字以内で入力してください',
            'description.required'=> 'あらすじは必須です',
            'description.max'=> 'あらすじは1000文字以内で入力してください',
        ];
    }

    /**
     * リクエストデータの前処理
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'author_name' => $this->author_name ?: 'kerok'
        ]);
    }
}
