<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class FeaturePlaceholderController extends Controller
{
    public function __invoke(string $feature): RedirectResponse
    {
        $features = config('healo.features');

        abort_unless(array_key_exists($feature, $features), 404);

        return redirect()->route($features[$feature]['route']);
    }
}
