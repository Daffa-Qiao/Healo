<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteHealthPlanTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('complete', $this->route('task')) ?? false;
    }

    public function rules(): array
    {
        return [
            'progress' => ['prohibited'],
            'status' => ['prohibited'],
            'user_id' => ['prohibited'],
            'task_type' => ['prohibited'],
        ];
    }
}
