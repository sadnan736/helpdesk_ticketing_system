<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
    </x-slot>

    @php
        $stats = [
            ['label' => 'Open', 'value' => $open, 'color' => 'text-blue-600'],
            ['label' => 'Resolved', 'value' => $resolved, 'color' => 'text-green-600'],
            ['label' => 'SLA breached', 'value' => $breached, 'color' => 'text-red-600'],
            ['label' => 'Escalated', 'value' => $escalated, 'color' => 'text-amber-600'],
        ];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-500">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-3xl font-semibold {{ $stat['color'] }}">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('tickets.index') }}" class="mt-6 inline-block text-sm text-indigo-600 hover:underline">View all tickets</a>
        </div>
    </div>
</x-app-layout>