<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TraitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $traits = [
            'Ability to communicate openly',
            'Ability to show respect',
            'Ability to value your and their opinions',
            'Maintaining transparency',
            'Been faithful?',
            'Was your partner forgiving',
            'Understanding and sharing your feelings',
            'Ability to express and appreciate',
            'Understanding personal space and boundaries',
            'Managing stress',
            'Managing finances',
            'Managing time',
            'Ability to resolve conflicts in a healthy manner',
            'Using humor',
            'Ability to compromise',
            'How dependent your partner was',
            'Ability to listen',
        ];

        foreach ($traits as $trait) {
            \App\Models\CustomTrait::create(['name' => $trait]);
        }
    }
}
