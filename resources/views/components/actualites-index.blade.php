@foreach($actualites as $actu)
@if($actualites->isEmpty())
<p class="text-center text-gray-500">{{__('aucune_actualite')}}</p>
@else
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    @if ($actu->type == 'Publique')
    <div class="bg-white rounded-2xl shadow p-5">
        <h3 class="font-semibold mb-2">{{ $actu->titre }}</h3>
        <p class="text-gray-700 mb-3">{{ $actu->description }}</p>
        <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($actu->dateP)->format('d/m/Y') }}</p>
        @if($actu->image)
            <img src="{{ asset('storage/' . $actu->image) }}" alt="{{ $actu->titre }}" class="rounded-lg mb-4">
        @endif
    </div>
@endif
</div>
@endif
@endforeach