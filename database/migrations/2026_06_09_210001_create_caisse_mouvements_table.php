<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisse_mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('caisse_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['entree', 'sortie']);
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse_mouvements');
    }
};
