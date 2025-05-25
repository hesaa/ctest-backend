<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends BaseController
{
    public function index(Request $request)
    {
        $query = Employee::query();

        // Filter by name
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');

        if (in_array($sort, ['id', 'name']) && in_array(strtolower($order), ['asc', 'desc'])) {
            $query->orderBy($sort, $order);
        }

        // Pagination (default 10 per page)
        $perPage = (int) $request->get('show', 10);

        $data = $query->paginate($perPage);
        return $this->paginatedResponse($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 422);
        }


        $employee = Employee::create($validator->validated());

        return $this->sendResponse($employee, 201);
    }

    public function show(Employee $employee)
    {
        return $this->sendResponse($employee, 200);
    }

    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee->update($validator->validated());
        return $this->sendResponse($employee, 200, 'Request has been saved successfully');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return $this->sendResponse($employee, 200);
    }
}
