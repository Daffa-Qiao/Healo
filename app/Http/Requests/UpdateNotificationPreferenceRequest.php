<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notifications_enabled' => ['sometimes', 'boolean'],
            'health_plan_enabled' => ['sometimes', 'boolean'],
            'meal_reminder_enabled' => ['sometimes', 'boolean'],
            'journal_reminder_enabled' => ['sometimes', 'boolean'],
            'health_goal_enabled' => ['sometimes', 'boolean'],
            'health_insight_enabled' => ['sometimes', 'boolean'],
            'wellness_enabled' => ['sometimes', 'boolean'],
            'system_enabled' => ['sometimes', 'boolean'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i', 'required_with:quiet_hours_end'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i', 'required_with:quiet_hours_start'],
            'reminder_time' => ['sometimes', 'required', 'date_format:H:i'],
        ];
    }
}
