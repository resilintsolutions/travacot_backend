<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permissions.index', [
            'permissions' => Permission::orderBy('name')->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required','string','max:255','unique:permissions,name']]);
        Permission::create(['name' => $data['name']]);
        return redirect()->route('admin.permissions.index')->with('status','Permission created.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return back()->with('status','Permission deleted.');
    }
}
