<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) { $table->id(); $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete(); $table->string('name'); $table->text('description')->nullable(); $table->date('deadline')->nullable(); $table->timestamps(); });
        Schema::create('project_user', function (Blueprint $table) { $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('role')->default('member'); $table->unique(['project_id', 'user_id']); });
        Schema::create('milestones', function (Blueprint $table) { $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->date('due_date')->nullable(); $table->boolean('completed')->default(false); $table->timestamps(); });
        Schema::create('tasks', function (Blueprint $table) { $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('title'); $table->text('description')->nullable(); $table->string('priority')->default('medium'); $table->string('status')->default('todo'); $table->date('due_date')->nullable(); $table->timestamps(); });
        Schema::create('task_comments', function (Blueprint $table) { $table->id(); $table->foreignId('task_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->text('body'); $table->timestamps(); });
        Schema::create('activity_logs', function (Blueprint $table) { $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('event'); $table->json('details')->nullable(); $table->timestamps(); });
    }
    public function down(): void
    {
        Schema::dropIfExists('activity_logs'); Schema::dropIfExists('task_comments'); Schema::dropIfExists('tasks'); Schema::dropIfExists('milestones'); Schema::dropIfExists('project_user'); Schema::dropIfExists('projects');
    }
};
