<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areaCentral = Area::all()->first();

        $cov = User::create([
            'name' => 'Оператор ЦОВ',
            'uid' => 100001,
            'password' => Hash::make('123456'),
            'area_id' => null,
            'call_type_id' => null,
        ]);
        $cov->assignRole('cov_112');

        if ($areaCentral) {
            $op01 = User::create([
                'name' => 'Диспетчер 01',
                'uid' => 200002,
                'password' => Hash::make('123456'),
                'area_id' => $areaCentral->id,
                'call_type_id' => 4
            ]);
            $op01->assignRole('op_01');
        }

        if ($areaCentral) {
            $edds = User::create([
                'name' => 'Оператор ЕДДС',
                'uid' => 300003,
                'password' => Hash::make('123456'),
                'area_id' => $areaCentral->id,
                'call_type_id' => 8
            ]);
            $edds->assignRole('edds');
        }
    }
}
