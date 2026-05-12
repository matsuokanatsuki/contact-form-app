<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;


class AdminController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $query = Contact::query()
           ->filter($request->validated());

        $contacts = $query
            ->latest()
            ->paginate(7)
            ->appends($request->query());

        return view('admin.index', [
            'contacts' => $contacts,
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin')
            ->with('success', 'お問い合わせを削除しました');
    }
}