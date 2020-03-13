<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Resources\TaskResource;
use App\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('horizon_token') !== config('horizon.access_token')) {
            abort(403);
        }

        $tasks = Task::query()
            ->orderBy('created_at', 'desc')
            ->paginate();

        return TaskResource::collection($tasks);
    }
}
