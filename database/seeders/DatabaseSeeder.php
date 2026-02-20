<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\CallType;
use App\Models\District;
use App\Models\EmergencyType;
use App\Models\IncidentType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Admin',
//            'email' => 'admin@mail.ru',
//            'password' => bcrypt('admin123'),
//        ]);

        $incidentTypes = [
            'Лица в розыске',
            'Лифты / не работает лифт',
            'Лохотрон / незаконное ведение азартных игр',
            'Лифты / остановка лифта с пассажиром в кабине',
            'Лифты / неисправность, дефекты лифтового оборудования',
            'Лифты / умышленное повреждение лифтового оборудования',
            'Плохо человеку',
            'Аварии на авиотранспорте',
            'Аварии на взрыво-пожарных объектах',
            'Аварии на ж/д транспорте',
            'Аварии на электроподстанции',
            'Аварии с выбросом АХОВ',
            'Автоподстава',
            'Атмосферны осадки / град, ливень',
        ];
        foreach ($incidentTypes as $type) {
            IncidentType::create([
                'name' => $type,
            ]);
        }

        $callTypes = [
            'Ложный',
            'Детская шалость',
            'Справочный',
            'Пожарные',
            'Полиция',
            'Скорая',
            'Служба газа',
            'ЕДДС',
        ];
        foreach ($callTypes as $type) {
            CallType::create([
                'name' => $type
            ]);
        }

        $areas = [
            'Алтайский' => [
                'с. Ая',
                'с. Верх-Ая',
                'пос. Катунь',
            ],
            'Аскизский' => [
                'пос. Аскиз',
                'пос. Бискамжа',
                'пос. В-Тея',
                'с. Аскиз',
            ],
            'Бейский' => [
                'c. Бондарево'
            ],
            'Боградский' => [
                'Советская Хакасия',
                'Усть-Ерба'
            ],
            'Орджоникидзевский' => [
                'пос. Гайдаровск',
                'пос. Копьёво'
            ],
            'Таштыпский' => [
                'с. Таштып',
                'с. Большая Сея',
            ],
            'Усть-Абаканский' => [
                'аал Доможаков',
                'с. Сапогов',
                'пгт. Усть-Абакан'
            ],
            'Ширинский' => [
                'с. Ворота',
                'пос. Жемчужный'
            ]
        ];

        foreach ($areas as $areaName => $districts) {
            $area = Area::create([
                'name' => $areaName
            ]);
            foreach ($districts as $district) {
                District::create([
                    'name' => $district,
                    'area_id' => $area->id,
                ]);
            }
        }

        $emergencyTypes = ['Землятресение', 'паводки', 'катастрофа на жд', 'пожар'];
        foreach ($emergencyTypes as $type) {
            EmergencyType::create([
                'name' => $type,
            ]);
        }
    }
}
