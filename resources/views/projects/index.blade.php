<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Projects</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto px-4">
        <form method="post" action="{{ route('projects.store') }}" class="bg-white p-5 rounded shadow mb-6">@csrf
            <h3 class="font-bold mb-3">Create project</h3>
            <input name="name" required maxlength="120" placeholder="Project name" class="border rounded p-2">
            <input name="deadline" type="date" class="border rounded p-2">
            <textarea name="description" placeholder="Description" class="border rounded p-2"></textarea>
            <button class="bg-indigo-600 text-white p-2 rounded">Create</button>
            @error('name')<p class="text-red-700">{{ $message }}</p>@enderror
        </form>
        <div class="grid md:grid-cols-3 gap-4">
            @forelse($projects as $project)
                <a class="bg-white p-5 rounded shadow hover:ring-2" href="{{ route('projects.show',$project) }}">
                    <h3 class="font-bold text-lg">{{ $project->name }}</h3><p>{{ $project->description }}</p>
                    <p class="text-sm text-gray-600">{{ $project->tasks_count }} tasks · due {{ $project->deadline?->format('M j, Y') ?? 'not set' }}</p>
                </a>
            @empty<p>No projects yet.</p>@endforelse
        </div>
    </div>
</x-app-layout>
