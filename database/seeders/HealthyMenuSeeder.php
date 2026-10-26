<?php

namespace Database\Seeders;

use App\Models\HealthyMenu;
use Illuminate\Database\Seeder;

class HealthyMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['name' => 'Oat Pisang dan Yogurt', 'slug' => 'oat-pisang-yogurt', 'meal_type' => 'breakfast', 'diet_preference' => 'vegetarian', 'calories' => 380, 'protein_g' => 16, 'carbohydrates_g' => 58, 'fat_g' => 10, 'ingredients' => ['Oat 50 g', 'Pisang 1 buah', 'Yogurt plain 120 g'], 'preparation' => 'Campurkan oat dan yogurt, lalu tambahkan irisan pisang.'],
            ['name' => 'Nasi Merah Ayam Panggang', 'slug' => 'nasi-merah-ayam-panggang', 'meal_type' => 'lunch', 'diet_preference' => 'high_protein', 'calories' => 520, 'protein_g' => 38, 'carbohydrates_g' => 58, 'fat_g' => 14, 'ingredients' => ['Nasi merah 150 g', 'Dada ayam 120 g', 'Sayuran kukus 100 g'], 'preparation' => 'Panggang ayam tanpa banyak minyak dan sajikan dengan nasi serta sayuran.'],
            ['name' => 'Tumis Tahu Sayuran', 'slug' => 'tumis-tahu-sayuran', 'meal_type' => 'dinner', 'diet_preference' => 'vegan', 'calories' => 410, 'protein_g' => 22, 'carbohydrates_g' => 38, 'fat_g' => 18, 'ingredients' => ['Tahu 150 g', 'Brokoli 100 g', 'Wortel 60 g', 'Nasi putih 100 g'], 'preparation' => 'Tumis tahu dan sayuran dengan sedikit minyak, lalu sajikan bersama nasi.'],
            ['name' => 'Apel dan Yogurt Plain', 'slug' => 'apel-yogurt-plain', 'meal_type' => 'snack', 'diet_preference' => 'vegetarian', 'calories' => 190, 'protein_g' => 8, 'carbohydrates_g' => 32, 'fat_g' => 4, 'ingredients' => ['Apel 1 buah', 'Yogurt plain 100 g'], 'preparation' => 'Potong apel dan sajikan bersama yogurt plain.'],
            ['name' => 'Bubur Kacang Hijau Ringan', 'slug' => 'bubur-kacang-hijau-ringan', 'meal_type' => 'breakfast', 'diet_preference' => 'vegan', 'calories' => 330, 'protein_g' => 13, 'carbohydrates_g' => 58, 'fat_g' => 6, 'ingredients' => ['Kacang hijau 60 g', 'Santan encer 80 ml', 'Gula 10 g'], 'preparation' => 'Rebus kacang hijau hingga lunak dan gunakan santan encer secukupnya.'],
            ['name' => 'Ikan Panggang dan Kentang', 'slug' => 'ikan-panggang-kentang', 'meal_type' => 'dinner', 'diet_preference' => 'balanced', 'calories' => 470, 'protein_g' => 34, 'carbohydrates_g' => 44, 'fat_g' => 17, 'ingredients' => ['Ikan 130 g', 'Kentang 180 g', 'Salad sayur 100 g'], 'preparation' => 'Panggang ikan dan kentang, lalu sajikan dengan salad tanpa saus berlebih.'],
        ];

        foreach ($menus as $menu) {
            HealthyMenu::query()->updateOrCreate(['slug' => $menu['slug']], [
                ...$menu,
                'source' => 'HEALO internal sample',
                'attribution' => 'Nilai nutrisi merupakan estimasi data demo.',
                'active' => true,
            ]);
        }
    }
}
