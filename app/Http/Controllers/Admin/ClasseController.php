<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClasseController extends Controller
{
    public function index(): View
    {
        $classes = Classe::orderBy('idClasse')->get();

        return view('admin.classes.index', compact('classes'));
    }

    public function create(): View
    {
        return view('admin.classes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:20'],
            'niveau' => ['required', 'string', 'max:3'],
        ]);

        $nextId = (int) Classe::max('idClasse') + 1;

        Classe::create([
            'idClasse' => $nextId ?: 1,
            'nom' => $validated['nom'],
            'niveau' => $validated['niveau'],
        ]);

        return redirect()
            ->route('admin.classes.index')
            ->with('status', trans('admin.classes_page.messages.created'));
    }

    public function show(Classe $classe): View
    {
        $classe->load(['enfants' => function ($query) {
            $query->orderBy('nom')->orderBy('prenom');
        }]);

        return view('admin.classes.show', compact('classe'));
    }

    public function edit(Classe $classe): View
    {
        return view('admin.classes.edit', compact('classe'));
    }

    public function update(Request $request, Classe $classe): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
        ]);

        $classe->update($validated);

        return redirect()
            ->route('admin.classes.index')
            ->with('status', trans('admin.classes_page.messages.updated'));
    }

    public function destroy(Classe $classe): RedirectResponse
    {
        $classe->delete();

        return redirect()
            ->route('admin.classes.index')
            ->with('status', trans('admin.classes_page.messages.deleted'));
    }
}

