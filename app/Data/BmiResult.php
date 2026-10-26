<?php

namespace App\Data;

use App\Enums\BmiCategory;

readonly class BmiResult
{
    public function __construct(
        public float $value,
        public BmiCategory $category,
    ) {}
}
