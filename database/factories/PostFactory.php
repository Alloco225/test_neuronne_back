<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $created_at = $this->faker->dateTimeThisDecade();
        $last_update = Carbon::parse($created_at)->addDays(rand(0, 365));
        return [
            //
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => $this->faker->text(15),
            'content' => implode('\n', $this->faker->paragraphs(rand(3, 10))),
            'image_path' => $this->faker->imageUrl(),
            'created_at' => $created_at,
            'last_update' => $last_update,

        ];
    }
}
