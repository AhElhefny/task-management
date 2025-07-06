<?php

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)
                ->comment('pending= ' . TaskStatusEnum::PENDING->value .
                    ', completed= ' . TaskStatusEnum::COMPLETED->value . ', cancelled= ' . TaskStatusEnum::CANCELLED->value);
            $table->timestamp('due_date');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();

            // create indexes for columns that will be frequently queried to speed up sorting/filtering
            $table->index('status');
            $table->index('due_date');

            // create full-text index for title and description
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
