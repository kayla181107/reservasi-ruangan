<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            [
                'name'        => 'Ruang Rapat 1',
                'capacity'    => 20,
                'description' => 'Ruang rapat untuk meeting kecil',
            ],
            [
                'name'        => 'Ruang Rapat 2',
                'capacity'    => 25,
                'description' => 'Ruang rapat dengan proyektor',
            ],
            [
                'name'        => 'Aula Utama',
                'capacity'    => 100,
                'description' => 'Ruang besar untuk acara dan seminar',
            ],
            [
                'name'        => 'Ruang Training',
                'capacity'    => 40,
                'description' => 'Ruang pelatihan karyawan',
            ],
            [
                'name'        => 'Ruang Diskusi A',
                'capacity'    => 10,
                'description' => 'Ruang kecil untuk diskusi tim',
            ],
            [
                'name'        => 'Ruang Diskusi B',
                'capacity'    => 12,
                'description' => 'Ruang diskusi dengan papan tulis',
            ],
            [
                'name'        => 'Ruang Presentasi',
                'capacity'    => 50,
                'description' => 'Ruang untuk presentasi dan demo produk',
            ],
            [
                'name'        => 'Ruang Kreatif',
                'capacity'    => 15,
                'description' => 'Ruang dengan desain santai untuk brainstorming',
            ],
            [
                'name'        => 'Ruang IT Support',
                'capacity'    => 8,
                'description' => 'Ruang kerja tim IT support',
            ],
            [
                'name'        => 'Ruang Manajemen',
                'capacity'    => 30,
                'description' => 'Ruang meeting manajemen perusahaan',
            ],
        ]; 

        foreach ($rooms as $room) {
            Room::create([
                'name'        => $room['name'],
                'capacity'    => $room['capacity'],
                'description' => $room['description'],
                'status'      => 'inactive', 
            ]);
        }
    }
}
