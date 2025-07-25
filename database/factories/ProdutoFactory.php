<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produto>
 */
class ProdutoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $nome = $this->faker->unique()->word;
        return [
            'nome' => $nome,
            'descricao' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2),
            'slug' => Str::slug($nome), //Str é uma classe do Laravel disponível para auxiliar na formatação de strings e o slug() é um auxiliador que gera urls amigáveis.
            'imagem' => $this->faker->imageUrl(400, 400),
            'id_user' => User::pluck('id')->random(), // pluck() é uma função utilizada para pegar o campo da tabela que você deseja referenciar.
            'id_categoria' => Categoria::pluck('id')->random()
        ];
    }
}
