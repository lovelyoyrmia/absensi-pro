<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    // List all employees
    public function index() {
        $employees = User::where('role', 'employee')->get();
        return view('admin.employees.index', compact('employees'));
    }

    // Show the "Create" form
    public function create() {
        return view('admin.employees.create');
    }

    // Store the new employee in database
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $generatedNip = 'EMP-' . date('Y') . '-' . rand(1000, 9999);

        // Ensure it's unique (recursive check)
        while (User::where('nip', $generatedNip)->exists()) {
            $generatedNip = 'EMP-' . date('Y') . '-' . rand(1000, 9999);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nip' => $generatedNip,
            'department' => $request->department,
            'role' => 'employee',
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created!');
    }

    public function edit($id)
    {
        $employee = User::findOrFail($id);
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8',
            'department' => 'required|string|max:255',
        ]);

        $employee->name = $request->name;
        $employee->email = $request->email;
        $employee->department = $request->department;
        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password);
        }
        $employee->save();

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully!');
    }
}
