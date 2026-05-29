<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    public function run(): void
    {
        $forms = [
            [
                'name'    => 'Salt',
                'email'   => 'demo@salt.test',
                'phone'   => '+41 79 123 45 67',
                'address' => '1003 Lausanne',
                'message' => 'Nous aimerions organiser une collecte pour nos employés du siège.',
                'trophy'  => true,
                'treated' => false,
            ],
            [
                'name'    => 'BCV',
                'email'   => 'demo@bcv.test',
                'phone'   => '+41 21 212 10 10',
                'address' => '1001 Lausanne',
                'message' => null,
                'trophy'  => false,
                'treated' => false,
            ],
            [
                'name'    => 'Migros',
                'email'   => 'demo@migros.test',
                'phone'   => '+41 44 200 11 11',
                'address' => '1200 Genève',
                'message' => 'Intéressés par une collecte sur plusieurs sites.',
                'trophy'  => true,
                'treated' => true,
            ],
            [
                'name'    => 'Nestlé',
                'email'   => 'demo@nestle.test',
                'phone'   => '+41 21 924 11 11',
                'address' => '1800 Vevey',
                'message' => 'Projet de collecte pour la journée des collaborateurs.',
                'trophy'  => false,
                'treated' => false,
            ],
            [
                'name'    => 'EPFL',
                'email'   => 'demo@epfl.test',
                'phone'   => null,
                'address' => '1015 Lausanne',
                'message' => 'Collecte prévue en fin de semestre.',
                'trophy'  => true,
                'treated' => false,
            ],
        ];

        foreach ($forms as $form) {
            Form::create($form);
        }
    }
}