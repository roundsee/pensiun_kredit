<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = \App\Models\Account::with('group')->get();
        return view('products.accounts', compact('accounts'));
    }

    public function create()
    {
        $groups = \App\Models\AccountGroup::all();
        return view('products.create_account', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:20',
            'account_group_id' => 'required|exists:account_groups,id',
        ]);
        \App\Models\Account::create($validated);
        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit($id)
    {
        $account = \App\Models\Account::findOrFail($id);
        $groups = \App\Models\AccountGroup::all();
        return view('products.edit_account', compact('account', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $account = \App\Models\Account::findOrFail($id);
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:20',
            'account_group_id' => 'required|exists:account_groups,id',
        ]);
        $account->update($validated);
        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy($id)
    {
        $account = \App\Models\Account::findOrFail($id);
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
    }
}
