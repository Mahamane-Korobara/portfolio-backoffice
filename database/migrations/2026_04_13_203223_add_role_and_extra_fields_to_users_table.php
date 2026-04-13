<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'visitor'])->default('visitor')->after('email');
            $table->string('avatar')->nullable()->after('role');
            $table->string('google_id')->nullable()->unique()->after('avatar');
            $table->boolean('notify_new_articles')->default(true)->after('google_id');

            // Note : Pour modifier une colonne existante (change), 
            // assure-toi d'avoir installé doctrine/dbal via composer
            $table->timestamp('email_verified_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar', 'google_id', 'notify_new_articles']);
            // Note: On ne peut pas facilement "annuler" un ->change() sans redéfinir l'état précédent
        });
    }
};
