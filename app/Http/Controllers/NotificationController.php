<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationSetting;
use App\Models\Evenement;
use App\Models\DocumentObligatoire;

class NotificationController extends Controller
{
   
    public function index()
    {
        $settings = NotificationSetting::with('target')->latest()->get();
        return view('admin.notifications.index', compact('settings'));
    }

    
    public function create()
    {
        
        $evenements = Evenement::all(); 
        $documents = DocumentObligatoire::all();
        
        return view('admin.notifications.create', compact('evenements', 'documents'));
    }

    
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'module_id' => 'required|integer',
            'module_type' => 'required|string',
            'recurrence_days' => 'nullable|integer',
            'reminder_days' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'nullable', 
        ]);

       
        $targetClass = null;

        if ($request->module_type === 'Document') {
            $targetClass = \App\Models\DocumentObligatoire::class;
        } elseif ($request->module_type === 'Evènement') {
            $targetClass = \App\Models\Evenement::class;
        }

        
        if (!$targetClass) {
            return back()->withErrors(['module_type' => 'Type de module invalide.']);
        }

        
        NotificationSetting::create([
            'title' => $request->title,
            'description' => $request->description,
            'recurrence_days' => $request->recurrence_days,
            'reminder_days' => $request->reminder_days,
            'is_active' => $request->has('is_active'), 
            
            
            'target_id' => $request->module_id,
            'target_type' => $targetClass,
        ]);

       
        return redirect()->route('admin.notifications.index')
                         ->with('success', 'Notification ajoutée avec succès !');
    }

    public function edit($id)
{
 
    $setting = \App\Models\NotificationSetting::findOrFail($id);

    
    return view('admin.notifications.edit', compact('setting'));
}

public function update(Request $request, $id)
{
    
    $setting = \App\Models\NotificationSetting::findOrFail($id);
    
    
    $setting->update([
        'title' => $request->title,
        'description' => $request->description,
        'recurrence_days' => $request->recurrence_days,
        'reminder_days' => $request->reminder_days,
        'is_active' => $request->has('is_active'),
       
        'target_type' => $request->module_type == 'Document' ? 'App\Models\DocumentObligatoire' : 'App\Models\Evenement',
        'target_id' => $request->module_id,
    ]);

    return redirect()->route('admin.notifications.index')->with('success', 'Règle modifiée avec succès');
}

public function markAsRead($id)
{
   
    $notification = auth()->user()->notifications()->where('id', $id)->first();

    if ($notification) {
       
        $notification->markAsRead();
    }

    
    return back();
}
}