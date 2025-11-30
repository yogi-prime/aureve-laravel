<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Shiprocket fields
            $table->string('shiprocket_order_id')->nullable()->after('tracking_number');
            $table->string('shiprocket_shipment_id')->nullable()->after('shiprocket_order_id');
            $table->string('awb_code')->nullable()->after('shiprocket_shipment_id');
            $table->string('courier_name')->nullable()->after('awb_code');
            $table->string('courier_id')->nullable()->after('courier_name');
            $table->timestamp('pickup_scheduled_at')->nullable()->after('courier_id');
            $table->decimal('shipping_weight', 8, 2)->nullable()->after('pickup_scheduled_at');
            $table->json('shiprocket_response')->nullable()->after('shipping_weight');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shiprocket_order_id',
                'shiprocket_shipment_id',
                'awb_code',
                'courier_name',
                'courier_id',
                'pickup_scheduled_at',
                'shipping_weight',
                'shiprocket_response'
            ]);
        });
    }
};
