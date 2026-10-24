<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_create_project_and_tasks_with_persisted_metrics(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/projects', ['name' => 'Release plan'])->assertRedirect();
        $project = Project::firstOrFail();
        $this->actingAs($user)->post("/projects/{$project->id}/tasks", ['title'=>'Ship feature','priority'=>'high','status'=>'todo'])->assertRedirect();
        $task = Task::firstOrFail();
        $this->actingAs($user)->patch("/projects/{$project->id}/tasks/{$task->id}", ['status'=>'done'])->assertRedirect();
        $this->actingAs($user)->get("/projects/{$project->id}")->assertOk()->assertSee('Ship feature')->assertSee('Done');
        $this->assertDatabaseHas('activity_logs', ['project_id'=>$project->id,'event'=>'Task status changed']);
    }

    public function test_non_member_cannot_read_project_or_modify_its_tasks(): void
    {
        $owner=User::factory()->create(); $other=User::factory()->create();
        $project=Project::create(['owner_id'=>$owner->id,'name'=>'Private']); $project->members()->attach($owner->id,['role'=>'owner']);
        $task=$project->tasks()->create(['title'=>'Private task','priority'=>'medium','status'=>'todo']);
        $this->actingAs($other)->get("/projects/{$project->id}")->assertNotFound();
        $this->actingAs($other)->patch("/projects/{$project->id}/tasks/{$task->id}", ['status'=>'done'])->assertNotFound();
        $this->assertDatabaseHas('tasks',['id'=>$task->id,'status'=>'todo']);
    }

    public function test_assignee_must_be_a_project_member_and_status_is_validated(): void
    {
        $owner=User::factory()->create(); $stranger=User::factory()->create();
        $project=Project::create(['owner_id'=>$owner->id,'name'=>'Scoped']); $project->members()->attach($owner->id,['role'=>'owner']);
        $this->actingAs($owner)->post("/projects/{$project->id}/tasks", ['title'=>'Bad assignee','priority'=>'medium','status'=>'todo','assignee_id'=>$stranger->id])->assertUnprocessable();
        $task=$project->tasks()->create(['title'=>'Valid','priority'=>'medium','status'=>'todo']);
        $this->actingAs($owner)->patch("/projects/{$project->id}/tasks/{$task->id}", ['status'=>'invented'])->assertSessionHasErrors('status');
    }
}
