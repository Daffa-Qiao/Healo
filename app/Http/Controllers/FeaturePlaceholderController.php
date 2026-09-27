<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class FeaturePlaceholderController extends Controller
{
    public function __invoke(string $feature): View
    {
        $features = config('healo.features');

        abort_unless(array_key_exists($feature, $features), 404);

        return view('features.placeholder', [
            'feature' => $features[$feature],
        ]);
    }
}
