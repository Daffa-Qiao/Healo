<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FoodAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:'.config('nutrition.image_max_kilobytes', 5120),
            ],
        ];
    }
}
