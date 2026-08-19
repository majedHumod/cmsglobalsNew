<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->string('tab')->toString();
        if (! in_array($tab, ['all', 'mine', 'stats'], true)) {
            $tab = 'all';
        }

        if ($tab === 'mine' && ! auth()->user()->hasRole('admin')) {
            $tab = 'all';
        }

        $scoped = function () {
            return Note::query()
                ->when(! auth()->user()->hasRole('admin'), function ($query) {
                    return $query->where('user_id', auth()->id());
                });
        };

        $stats = [
            'total' => $scoped()->count(),
            'mine' => Note::query()->where('user_id', auth()->id())->count(),
            'month' => $scoped()->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        $notesQuery = Note::with('user')
            ->when(! auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->when($tab === 'mine', function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->latest();

        $notes = $tab === 'stats'
            ? $notesQuery->take(6)->get()
            : $notesQuery->paginate(12)->withQueryString();

        return view('notes.index', compact('notes', 'stats', 'tab'));
    }

    public function create()
    {
        return view('notes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $note = new Note($validated);
        $note->user_id = auth()->id();
        $note->save();

        return redirect()->route('notes.index')->with('success', 'تم إنشاء الملاحظة بنجاح.');
    }

    public function edit(Note $note)
    {
        if (! auth()->user()->hasRole('admin') && $note->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بتعديل هذه الملاحظة.');
        }

        $note->loadMissing('user');

        return view('notes.edit', compact('note'));
    }

    public function update(Request $request, Note $note)
    {
        if (! auth()->user()->hasRole('admin') && $note->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بتعديل هذه الملاحظة.');
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $note->update($validated);

        return redirect()->route('notes.index')->with('success', 'تم تحديث الملاحظة بنجاح.');
    }

    public function destroy(Note $note)
    {
        if (! auth()->user()->hasRole('admin') && $note->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بحذف هذه الملاحظة.');
        }

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'تم حذف الملاحظة بنجاح.');
    }
}
