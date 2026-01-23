<x-app-layout>
    <div class="container py-5">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-0">Jakinarazpenak</h2>
                <small class="text-muted">Notifications</small>
            </div>
            <div class="d-flex justify-content-end gap-3 mt-5">
                        <a href="{{ route('admin.notifications.create') }}" class="btn text-white px-4 fw-bold" style="background-color: #F59E0B;">Ajouter</a>
                     
                    </div>
            
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3" style="width: 25%;">
                                    Izenburua <div class="text-muted small fw-normal">Titre</div>
                                </th>
                                <th style="width: 15%;">
                                    Modulua <div class="text-muted small fw-normal">Module</div>
                                </th>
                                <th style="width: 25%;">
                                    Deskribapena <div class="text-muted small fw-normal">Description</div>
                                </th>
                                <th class="text-center" style="width: 10%;">
                                    Errepikatzea <div class="text-muted small fw-normal">Récurrence</div>
                                </th>
                                <th class="text-center" style="width: 10%;">
                                    Oroigarria <div class="text-muted small fw-normal">Rappel</div>
                                </th>
                                <th class="text-center" style="width: 5%;">
                                    Gaituta <div class="text-muted small fw-normal">Activé</div>
                                </th>
                                <th class="text-end pe-4" style="width: 10%;">
                                    Ekintzak <div class="text-muted small fw-normal">Actions</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $setting)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    {{ $setting->title }}
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $setting->module_label }}
                                    </span>
                                </td>

                                <td class="text-muted">
                                    {{ Str::limit($setting->description, 30) }}
                                </td>

                                <td class="text-center">
                                    @if($setting->recurrence_days)
                                        {{ $setting->recurrence_days }} jours
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="text-center fw-bold">
                                    {{ $setting->reminder_days }} jours
                                </td>

                                <td class="text-center">
                                    @if($setting->is_active)
                                        <i class="bi bi-check-lg fw-bold fs-4 text-success"></i>
                                    @else
                                        <i class="bi bi-dash-lg fs-4 text-muted"></i>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="#" class="text-dark">
                                            <i class="bi bi-eye fs-5"></i>
                                        </a>
                                        <a href="#" class="text-dark">
                                            <i class="bi bi-pencil-fill fs-6"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                                    Aucune règle de notification configurée.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>