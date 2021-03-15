<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentMethod::truncate();
        PaymentMethod::create([
            'name' => 'Webcheckout',
            'image' => 'img/payment_methods/webcheckout.png',
        ]);
        PaymentMethod::create([
            'name' => 'PSE',
            'image' => 'img/payment_methods/pse.png',
        ]);
    }
}
