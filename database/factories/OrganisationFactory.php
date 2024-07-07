<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganisationFactory extends Factory
{
    protected $model = Organisation::class;

    public function definition()
    {
        return [
            'orgId' => Str::uuid(),
            'name' => $this->faker->company,
            'description' => $this->faker->sentence,
        ];
    }
}
