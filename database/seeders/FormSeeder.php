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
                'name'     => 'Salt',
                'email'    => 'demo@salt.test',
                'phone'    => '+41 79 123 45 67',
                'address'  => 'Avenue du Léman 12',
                'zip_code' => '1003',
                'locality' => 'Lausanne',
                'message'  => 'Nous aimerions organiser une collecte pour nos employés du siège.',
                'trophy'   => true,
                'treated'  => false,
            ],
            [
                'name'     => 'BCV',
                'email'    => 'demo@bcv.test',
                'phone'    => '+41 21 212 10 10',
                'address'  => 'Place Saint-François 14',
                'zip_code' => '1001',
                'locality' => 'Lausanne',
                'message'  => null,
                'trophy'   => false,
                'treated'  => false,
            ],
            [
                'name'     => 'Migros',
                'email'    => 'demo@migros.test',
                'phone'    => '+41 44 200 11 11',
                'address'  => 'Rue du Rhône 25',
                'zip_code' => '1200',
                'locality' => 'Genève',
                'message'  => 'Intéressés par une collecte sur plusieurs sites.',
                'trophy'   => true,
                'treated'  => true,
            ],
            [
                'name'     => 'Nestlé',
                'email'    => 'demo@nestle.test',
                'phone'    => '+41 21 924 11 11',
                'address'  => 'Avenue Nestlé 55',
                'zip_code' => '1800',
                'locality' => 'Vevey',
                'message'  => 'Projet de collecte pour la journée des collaborateurs.',
                'trophy'   => false,
                'treated'  => false,
            ],
            [
                'name'     => 'EPFL',
                'email'    => 'demo@epfl.test',
                'phone'    => null,
                'address'  => 'Route Cantonale 15',
                'zip_code' => '1015',
                'locality' => 'Lausanne',
                'message'  => 'Collecte prévue en fin de semestre.',
                'trophy'   => true,
                'treated'  => false,
            ],
        ];

        foreach ($forms as $form) {
            Form::create($form);
        }
    }
}