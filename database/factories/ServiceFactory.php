<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->name(),
            'price_min' => "100",
            'price_max' => "1000",
            'location' => 'Westlandds',
            'location_lat' => '-1.269485',
            'location_long' => '36.609384'
,        ];
    }
}
