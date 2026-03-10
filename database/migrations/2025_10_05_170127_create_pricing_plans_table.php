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
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price')->default(0);
            $table->json('role')->default('user');
            $table->boolean('can_create_tenant')->default(false);
            $table->boolean('can_edit_tenant')->default(false);
            $table->boolean('can_delete_tenant')->default(false);
            $table->boolean('can_create_tasks')->default(false);
            $table->boolean('can_create_projects')->default(false);
            $table->boolean('can_edit_tasks')->default(false);
            $table->boolean('can_edit_projects')->default(false);
            $table->boolean('can_delete_tasks')->default(false);
            $table->boolean('can_delete_projects')->default(false);
            $table->boolean('can_invite_members')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
