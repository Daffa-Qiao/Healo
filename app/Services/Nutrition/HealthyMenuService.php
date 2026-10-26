<?php

namespace App\Services\Nutrition;

use App\Models\HealthyMenu;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HealthyMenuService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)
            ->orderBy('meal_type')
            ->orderBy('calories')
            ->paginate(12)
            ->withQueryString();
    }

    /** @return Collection<int, HealthyMenu> */
    public function recommendations(User $user, ?string $mealType = null): Collection
    {
        $profile = $user->nutritionProfile;
        $target = (int) ($profile?->daily_calorie_target ?? 2000);
        $mealShare = $mealType === 'snack' ? 0.12 : 0.3;
        $desired = $target * $mealShare;
        $filters = [
            'meal_type' => $mealType,
            'diet_preference' => $profile?->diet_preference?->value,
            'min_calories' => max(0, $desired - 180),
            'max_calories' => $desired + 180,
        ];
        $allergies = collect($user->healthProfile?->allergies ?? [])->map(fn ($value) => mb_strtolower($value));

        return $this->query($filters)
            ->orderByRaw('ABS(calories - ?)', [$desired])
            ->limit(12)
            ->get()
            ->reject(function (HealthyMenu $menu) use ($allergies): bool {
                $ingredients = mb_strtolower(implode(' ', $menu->ingredients));

                return $allergies->contains(fn (string $allergy): bool => $allergy !== '' && str_contains($ingredients, $allergy));
            })
            ->take(3)
            ->values();
    }

    /** @param array<string, mixed> $filters */
    private function query(array $filters): Builder
    {
        return HealthyMenu::query()
            ->where('active', true)
            ->when($filters['q'] ?? null, fn (Builder $query, string $search) => $query->whereRaw(
                'LOWER(name) LIKE ?',
                ['%'.mb_strtolower($search).'%'],
            ))
            ->when($filters['meal_type'] ?? null, fn (Builder $query, string $type) => $query->where('meal_type', $type))
            ->when($filters['diet_preference'] ?? null, function (Builder $query, string $preference): void {
                if ($preference === 'vegetarian') {
                    $query->whereIn('diet_preference', ['vegetarian', 'vegan']);
                } elseif ($preference !== 'balanced') {
                    $query->where('diet_preference', $preference);
                }
            })
            ->when(isset($filters['min_calories']), fn (Builder $query) => $query->where('calories', '>=', $filters['min_calories']))
            ->when(isset($filters['max_calories']), fn (Builder $query) => $query->where('calories', '<=', $filters['max_calories']));
    }
}
