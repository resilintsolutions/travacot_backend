<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ContactController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:Read Contact')->only(['index','show']);
        $this->middleware('permission:Create Contact')->only(['create','store']);
        $this->middleware('permission:Edit Contact')->only(['edit','update']);
        $this->middleware('permission:Delete Contact')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $contacts = Contact::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'email'   => ['nullable','string','email','max:255','unique:contacts,email'],
            'phone'   => ['nullable','string','max:50'],
            'company' => ['nullable','string','max:255'],
            'notes'   => ['nullable','string'],
        ]);

        $contact = new Contact();
        $contact->name    = $data['name'];
        $contact->email   = $data['email'] ?? null;
        $contact->phone   = $data['phone'] ?? null;
        $contact->company = $data['company'] ?? null;
        $contact->notes   = $data['notes'] ?? null;
        $contact->save();

        return redirect()->route('contacts.index')->with('status', 'Contact created.');
    }

    public function show(Contact $contact)
    {
        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'email'   => ['nullable','string','email','max:255','unique:contacts,email,'.$contact->id],
            'phone'   => ['nullable','string','max:50'],
            'company' => ['nullable','string','max:255'],
            'notes'   => ['nullable','string'],
        ]);

        $contact->name    = $data['name'];
        $contact->email   = $data['email'] ?? null;
        $contact->phone   = $data['phone'] ?? null;
        $contact->company = $data['company'] ?? null;
        $contact->notes   = $data['notes'] ?? null;
        $contact->save();

        return redirect()->route('contacts.show', $contact)->with('status', 'Contact updated.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('status', 'Contact deleted.');
    }
}
