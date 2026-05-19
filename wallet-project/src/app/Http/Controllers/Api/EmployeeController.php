<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $employees = Employee::with('wallets')
            ->latest()
            ->paginate(15);

        return EmployeeResource::collection($employees);
    }

    public function store(StoreEmployeeRequest $request): EmployeeResource
    {
        $employee = Employee::create([
            'external_employee_id' => $request->external_employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status ?? 'active',
        ]);

        return new EmployeeResource($employee);
    }

    public function show(Employee $employee): EmployeeResource
    {
        $employee->load('wallets');

        return new EmployeeResource($employee);
    }
}
