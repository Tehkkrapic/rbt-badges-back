<?php

namespace Database\Seeders;

use App\Models\Blockchain;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlockchainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Blockchain::truncate();
        $factory = Blockchain::factory();

        $factory->create([
            'name' => 'Ethereum',
            'code' => 'ETHEREUM',
        ]);
        $factory->create([
            'name' => 'Algorand',
            'code' => 'ALGORAND',
        ]);
        $factory->create([
            'name' => 'Near',
            'code' => 'NEAR',
        ]);
        
        $factory->create([
            'name' => 'Cardano',
            'code' => 'CARDANO',
        ]);
    }
}
