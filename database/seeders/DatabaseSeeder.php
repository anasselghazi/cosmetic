<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
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
        
       User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'client',
            'email' => 'client@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);


        $products = [
            ['name'=>'Crème hydratante','slug'=>'creme-hydratante','price'=>24.90,'category'=>'Visage','stock'=>50],
            ['name'=>'Huile argan','slug'=>'huile-argan','price'=>18.50,'category'=>'Huile','stock'=>30],
            ['name'=>'Sérum vitamine C','slug'=>'serum-vitamine-c','price'=>34.00,'category'=>'Visage','stock'=>25],
            ['name'=>'Masque argile','slug'=>'masque-argile','price'=>12.00,'category'=>'Masque','stock'=>40],
            ['name'=>'Lotion tonique','slug'=>'lotion-tonique','price'=>15.90,'category'=>'Visage','stock'=>60],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }
        
    }
}
