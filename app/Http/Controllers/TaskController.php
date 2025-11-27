<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = Task::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $tasks], 200);
    }

    // POST /api/tasks
    // Create a new task for the authenticated user.
    // Validation rules: title required, priority must be one of Low/Medium/High.
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'nullable|date',
        ]);

        // default initial status for new tasks
        $data['status'] = Task::STATUS_DRAFT;
        // always associate created tasks with the currently authenticated user
        $data['user_id'] = $request->user()->id;
        // mass-create the task (fillable fields defined in model)
        $task = Task::create($data);

        return response()->json(['data' => $task], 201);
    }

    // GET /api/tasks/{id}
    // Show a single task only if it belongs to the authenticated user.
    public function show($id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->first();
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        return response()->json(['data' => $task], 200);
    }

    // PUT /api/tasks/{id}
    // Update is allowed only when a task is in Draft state.
    // Validation rule uses 'sometimes' so partial updates are supported.
    public function update(Request $request, $id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->first();
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        if ($task->status !== Task::STATUS_DRAFT) {
            return response()->json(['message' => 'Only draft tasks can be updated'], 400);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:Low,Medium,High',
            'due_date' => 'nullable|date',
        ]);

        $task->update($data);
        return response()->json(['data' => $task], 200);
    }

    // POST /api/tasks/{id}/in-process
    // Move a task from Draft -> In-Process. Only the owner can perform this.
    public function inProcess($id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->first();
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        if ($task->status !== Task::STATUS_DRAFT) {
            return response()->json(['message' => 'Only draft tasks can be moved to in-process'], 400);
        }

        $task->status = Task::STATUS_IN_PROCESS;
        $task->save();
        return response()->json(['data' => $task], 200);
    }

    // POST /api/tasks/{id}/complete
    // Move a task from In-Process -> Completed. Only allowed when In-Process.
    public function complete($id)
    {
        $task = Task::where('id', $id)->where('user_id', auth()->id())->first();
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        if ($task->status !== Task::STATUS_IN_PROCESS) {
            return response()->json(['message' => 'Only in-process tasks can be completed'], 400);
        }

        $task->status = Task::STATUS_COMPLETED;
        $task->save();
        return response()->json(['data' => $task], 200);
    }
}
