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
            $table->foreignId('person_id')->after('id')->nullable()->constrained('people')->cascadeOnDelete();
            $table->dropColumn('name');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->foreignId('person_id')->after('id')->nullable()->constrained('people')->cascadeOnDelete();
            $table->dropColumn(['name', 'email', 'phone', 'document']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('person_id')->nullable();
            $table->dropConstrainedForeignId('person_id');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->string('name')->after('person_id')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('document')->nullable();
            $table->dropConstrainedForeignId('person_id');
        });
    }
};
