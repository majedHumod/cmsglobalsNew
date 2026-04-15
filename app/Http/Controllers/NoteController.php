<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $notesQuery = Note::with('user')->latest();

        if (! auth()->user()->hasRole('admin')) {
            $notesQuery->where('user_id', auth()->id());
        }

        $notes = $notesQuery->get();
        return view('notes.index', compact('notes'));
    }

    public function create()
    {
        return view('notes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required'
        ]);

        $note = new Note($validated);
        $note->user_id = auth()->id();
        $note->save();

        return redirect()->route('notes.index')->with('success', 'Note created successfully.');
    }

    public function edit(Note $note)
    {
        if (! auth()->user()->hasRole('admin') && $note->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بتعديل هذه الملاحظة.');
        }

        return view('notes.edit', compact('note'));
    }

    public function update(Request $request, Note $note)
    {
        if (! auth()->user()->hasRole('admin') && $note->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بتعديل هذه الملاحظة.');
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required'
        ]);

        $note->update($validated);

        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    public function destroy(Note $note)
    {
        if (! auth()->user()->hasRole('admin') && $note->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بحذف هذه الملاحظة.');
        }

        $note->delete();
        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }
}