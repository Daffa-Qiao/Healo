<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UncompleteHealthPlanTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('uncomplete', $this->route('task')) ?? false;
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
