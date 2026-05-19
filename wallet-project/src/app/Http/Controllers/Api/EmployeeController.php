<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
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

    public function store(CreateEmployeeRequest $request): EmployeeResource
    {
        $employee = Employee::create(
            $request->validated()
        );

        return new EmployeeResource($employee);
    }

    public function show(Employee $employee): EmployeeResource
    {
        $employee->load('wallets');

        return new EmployeeResource($employee);
    }
}
