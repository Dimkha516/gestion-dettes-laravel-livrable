<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{

    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'telephone' => $this->faker->unique()->phoneNumber,
            'adresse' => $this->faker->address,
            'user_id' => 1,
            // 'user_id' => null, // Par défaut, pas de compte utilisateur
        ];
    }

    public function withUser()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory(), // Crée un utilisateur associé avec la Factory User
            ];
        });
    }
}
