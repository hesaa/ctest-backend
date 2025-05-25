<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\TaskAssignment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskAssignmentController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'employee_id' => 'required|exists:employees,id',
            'hours_spent' => 'required|numeric|min:0.1',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 422);
        }

        $input = $validator->validated();

        $task = Task::find($input['task_id']);

        $input['total'] = ($task->hourly_rate * $input['hours_spent']) + $task->additional_charges;

        $assignment = TaskAssignment::create($input);

        $this->recalculateRemuneration($assignment->task);

        $data =  $assignment->load('employee');

        return $this->sendResponse($data, 201);
    }

    public function update(Request $request, TaskAssignment $taskAssignment)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'nullable|exists:employees,id',
            'hours_spent' => 'nullable|numeric|min:0.1',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 422);
        }

        $input = $validator->validated();

        $taskAssignment->update($input);

        $this->recalculateRemuneration($taskAssignment->task);

        $update = $taskAssignment->load('employee');
        return $this->sendResponse($update, 200, 'Request has been saved successfully');
    }

    private function recalculateRemuneration(Task $task): void
    {
        $totalRemuneration = $task->taskAssignments()->sum('total');
        $task->update(['total_remuneration' => $totalRemuneration]);
    }
}
