<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(1);
                $table->timestamps(0);
                $table->timestamp('deleted_at')->nullable();
                $table->string('image')->nullable();
                $table->foreignId('organizer_id')->constrained('organizers');
            });
        } else {
            Schema::table('events', function (Blueprint $table) {
                if (!Schema::hasColumn('events', 'is_public')) {
                    $table->boolean('is_public')->default(1)->after('description');
                }
                
                if (!Schema::hasColumn('events', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable()->after('updated_at');
                }
                
                if (!Schema::hasColumn('events', 'image')) {
                    $table->string('image')->nullable()->after('deleted_at');
                }
                
                if (!Schema::hasColumn('events', 'organizer_id')) {
                    $table->foreignId('organizer_id')->constrained('organizers')->after('image');
                }
            });
        }
    }
    
    public function down()
    {
        if (Schema::hasTable('events')) {
            $columnsToCheck = ['is_public', 'deleted_at', 'image', 'organizer_id'];
            $columnsToRemove = [];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('events', $column)) {
                    $columnsToRemove[] = $column;
                }
            }
            
            if (!empty($columnsToRemove)) {
                Schema::table('events', function (Blueprint $table) use ($columnsToRemove) {
                    $table->dropColumn($columnsToRemove);
                });
            }
        }
    }
};