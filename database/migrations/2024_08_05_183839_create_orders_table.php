<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->boolean('is_claimed_by_sales_rep')->default(false);
            $table->boolean('is_claimed_by_setter')->default(false);

            $table->string('invoice_number');

            $table->timestamp('invoice_date');

            $table->string('full_name');
            $table->string('email');
            $table->string('product_name');

            $table->double('amount_excluding_vat');
            $table->double('amount_including_vat');

            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
