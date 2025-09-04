<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payements', function (Blueprint $table) {
            $table->string('payment_token')->nullable()->after('transaction_ref');
            $table->text('cinetpay_data')->nullable()->after('payment_token'); // Pour stocker la réponse complète
        });
    }

    public function down()
    {
        Schema::table('payements', function (Blueprint $table) {
            $table->dropColumn(['payment_token', 'cinetpay_data']);
        });
    }
};
