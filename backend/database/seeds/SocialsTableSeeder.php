<?php

use App\Social;
use Illuminate\Database\Seeder;

class SocialsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Social::class, 'facebook')->create();
        factory(Social::class, 'google')->create();
    }
}
