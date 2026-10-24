<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    private function project(Request $request, int $id): Project { return $request->user()->projects()->whereKey($id)->firstOrFail(); }
    public function index(Request $request) { $projects = $request->user()->projects()->withCount('tasks')->latest()->get(); return view('projects.index', compact('projects')); }
    public function store(Request $request) { $data = $request->validate(['name'=>'required|string|max:120','description'=>'nullable|string|max:5000','deadline'=>'nullable|date']); $project = DB::transaction(function () use ($request,$data) { $p=Project::create(['owner_id'=>$request->user()->id,...$data]); $p->members()->attach($request->user()->id,['role'=>'owner']); ActivityLog::create(['project_id'=>$p->id,'user_id'=>$request->user()->id,'event'=>'Project created']); return $p; }); return redirect()->route('projects.show',$project); }
    public function show(Request $request,int $id) { $project=$this->project($request,$id); $query=$project->tasks()->with(['assignee','comments']); if($request->filled('q')) $query->where('title','like','%'.$request->string('q').'%'); if($request->filled('status')) $query->where('status',$request->string('status')); $tasks=$query->get()->groupBy('status'); $members=$project->members()->get(); $milestones=$project->milestones()->orderBy('due_date')->get(); $counts=$project->tasks()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate','status'); return view('projects.show',compact('project','tasks','members','milestones','counts')); }
    public function task(Request $request,int $id) { $project=$this->project($request,$id); $data=$request->validate(['title'=>'required|string|max:180','description'=>'nullable|string|max:5000','priority'=>'required|in:low,medium,high','status'=>'required|in:todo,in_progress,review,done','due_date'=>'nullable|date','assignee_id'=>'nullable|integer']); if(!empty($data['assignee_id'])&&!$project->members()->whereKey($data['assignee_id'])->exists()) abort(422,'Assignee must be a project member.'); $task=$project->tasks()->create($data); ActivityLog::create(['project_id'=>$project->id,'user_id'=>$request->user()->id,'event'=>'Task created','details'=>['task'=>$task->title]]); return back(); }
    public function updateTask(Request $request,int $id,int $task) { $project=$this->project($request,$id); $record=$project->tasks()->findOrFail($task); $data=$request->validate(['status'=>'required|in:todo,in_progress,review,done']); $old=$record->status; $record->update($data); ActivityLog::create(['project_id'=>$project->id,'user_id'=>$request->user()->id,'event'=>'Task status changed','details'=>['task'=>$record->title,'from'=>$old,'to'=>$record->status]]); return back(); }
    public function comment(Request $request,int $id,int $task) { $project=$this->project($request,$id); $record=$project->tasks()->findOrFail($task); $data=$request->validate(['body'=>'required|string|max:2000']); TaskComment::create(['task_id'=>$record->id,'user_id'=>$request->user()->id,...$data]); ActivityLog::create(['project_id'=>$project->id,'user_id'=>$request->user()->id,'event'=>'Comment added','details'=>['task'=>$record->title]]); return back(); }
    public function milestone(Request $request,int $id) { $project=$this->project($request,$id); $data=$request->validate(['name'=>'required|string|max:120','due_date'=>'nullable|date']); Milestone::create(['project_id'=>$project->id,...$data]); ActivityLog::create(['project_id'=>$project->id,'user_id'=>$request->user()->id,'event'=>'Milestone created']); return back(); }
}
