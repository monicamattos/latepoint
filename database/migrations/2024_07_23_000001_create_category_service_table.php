<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['service_id', 'category_id']);
        });

        DB::table('services')
            ->orderBy('id')
            ->whereNotNull('category_id')
            ->where('category_id', '!=', '')
            ->chunkById(100, function ($services) {
                foreach ($services as $service) {
                    if (in_array($service->category_id, ['0', 0], true)) {
                        continue;
                    }

                    DB::table('category_service')->updateOrInsert(
                        [
                            'service_id' => $service->id,
                            'category_id' => (int) $service->category_id,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_service');
    }
};
