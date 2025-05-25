<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Validator;

class TaskController extends BaseController
{
    public function index(Request $request)
    {
        $query = Task::with('taskAssignments.employee');

        // Filter by task description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');

        if (in_array($sort, ['id', 'description', 'date', 'hourly_rate', 'additional_charges', 'total_remuneration']) && in_array(strtolower($order), ['asc', 'desc'])) {
            $query->orderBy($sort, $order);
        }

        $perPage = (int) $request->get('show', 10);
        $pagination = $query->paginate($perPage);

        return $this->paginatedResponse($pagination, function ($task) {
            return [
                'id' => $task->id,
                'description' => $task->description,
                'date' => $task->date,
                'hourly_rate' => $task->hourly_rate,
                'additional_charges' => $task->additional_charges,
                'total_remuneration' => $task->total_remuneration,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'employees' => $task->taskAssignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->employee->id,
                        'name' => $assignment->employee->name,
                        'hours_spent' => $assignment->hours_spent,
                    ];
                }),
            ];
        });
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'date' => 'required|date',
            'hourly_rate' => 'required|numeric|min:0',
            'additional_charges' => 'nullable|numeric|min:0',
            'assignments' => 'nullable|array',
            'assignments.*.employee_id' => 'required_with:assignments|exists:employees,id',
            'assignments.*.hours_spent' => 'required_with:assignments|numeric|min:0.1',
        ]);


        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 422);
        }

        $input = $validator->validated();

        $trx = DB::transaction(function () use ($input) {

            $task = Task::create([
                'description' => $input['description'],
                'date' => $input['date'],
                'hourly_rate' => $input['hourly_rate'],
                'additional_charges' => $input['additional_charges'] ?? 0,
            ]);

            $totalHours = 0;

            if (!empty($input['assignments'])) {
                foreach ($input['assignments'] as $assignment) {
                    $assignment['total'] = ($assignment['hours_spent'] * $task->hourly_rate) + $task->additional_charges;
                    $task->taskAssignments()->create($assignment);
                    $totalHours += $assignment['hours_spent'];
                }
            }
            $totalRemuneration = collect($task->taskAssignments)->sum('total');
            $task->update(['total_remuneration' => $totalRemuneration]);

            return $task->load('taskAssignments.employee');
        });

        return $this->sendResponse($trx, 201);
    }

    public function show(Task $task)
    {
        $data =  $task->load('taskAssignments.employee');
        return $this->sendResponse($data);
    }

    public function update(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'date' => 'required|date',
            'hourly_rate' => 'required|numeric|min:0',
            'additional_charges' => 'nullable|numeric|min:0',
            'assignments' => 'nullable|array',
            'assignments.*.employee_id' => 'required_with:assignments|exists:employees,id',
            'assignments.*.hours_spent' => 'required_with:assignments|numeric|min:0.1',
        ]);


        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 422);
        }

        $input = $validator->validated();

        $update  = DB::transaction(function () use ($input, $task) {
            $task->update([
                'description' => $input['description'],
                'date' => $input['date'],
                'hourly_rate' => $input['hourly_rate'],
                'additional_charges' => $input['additional_charges'] ?? 0,
            ]);

            $task->taskAssignments()->delete();
            
            $totalRemuneration = 0;

            if (!empty($input['assignments'])) {

                foreach ($input['assignments'] as $assignment) {
                    $total = ($assignment['hours_spent'] * $task->hourly_rate) + $task->additional_charges;
                    $assignment['total'] = $total;
                    $task->taskAssignments()->create($assignment);
                    $totalRemuneration += $total;
                }
            }
            $task->update(['total_remuneration' => $totalRemuneration]);


            return $task->load('taskAssignments.employee');
        });

        return $this->sendResponse($update, 200, 'Request has been saved successfully');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return $this->sendResponse([], 200);
    }
}
