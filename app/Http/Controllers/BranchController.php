<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::orderBy('is_main', 'desc')->orderBy('name', 'asc')->get();
        return view('stock.branches', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_main' => 'nullable|boolean',
        ]);

        $isMain = $request->has('is_main') && $request->is_main;

        if ($isMain) {
            Branch::query()->update(['is_main' => false]);
        }

        Branch::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'phone' => $request->phone,
            'address' => $request->address,
            'is_main' => $isMain,
        ]);

        return redirect()->route('branches.index')->with('success', "Cabang / Gudang '{$request->name}' berhasil ditambahkan!");
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_main' => 'nullable|boolean',
        ]);

        $isMain = $request->has('is_main') && $request->is_main;

        if ($isMain && !$branch->is_main) {
            Branch::query()->update(['is_main' => false]);
        }

        $branch->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'phone' => $request->phone,
            'address' => $request->address,
            'is_main' => $isMain ? true : $branch->is_main,
        ]);

        return redirect()->route('branches.index')->with('success', "Data Cabang '{$branch->name}' berhasil diperbarui!");
    }

    public function destroy(Branch $branch)
    {
        if ($branch->is_main) {
            return redirect()->back()->with('error', 'Cabang Utama (Pusat) tidak dapat dihapus!');
        }

        $branch->delete();
        return redirect()->route('branches.index')->with('success', "Cabang '{$branch->name}' berhasil dihapus.");
    }
}
