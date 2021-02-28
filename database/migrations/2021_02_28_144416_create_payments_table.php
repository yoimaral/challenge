<?php

use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('status', 80)->default(Payment::STATUSES['IN PROCESS']);
            $table->string('reference')->nullable()->unique();
            $table->string('process_url')->nullable()->unique();
            $table->foreignId('payment_method_id')->constrained('payment_methods')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('amount');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
