@use('App\Models\Ticket')

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Tickets</h2>
            @if (auth()->user()->isCustomer())
                <a href="{{ route('tickets.create') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-500">New ticket</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="GET" class="mb-4">
                <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach (Ticket::STATUSES as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ str($s)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </form>

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Agent</th>
                            <th class="px-4 py-3">SLA due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tickets as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono">
                                    <a class="text-indigo-600 hover:underline" href="{{ route('tickets.show', $t) }}">{{ $t->code }}</a>
                                </td>
                                <td class="px-4 py-3">{{ $t->subject }}</td>
                                <td class="px-4 py-3">{{ $t->category }}</td>
                                <td class="px-4 py-3"><x-badge :type="$t->priorityColor()">{{ ucfirst($t->priority) }}</x-badge></td>
                                <td class="px-4 py-3"><x-badge :type="$t->statusColor()">{{ str($t->status)->replace('_', ' ')->title() }}</x-badge></td>
                                <td class="px-4 py-3">{{ $t->agent?->name ?? '-' }}</td>
                                <td class="px-4 py-3 {{ $t->isBreached() ? 'font-semibold text-red-600' : '' }}">{{ $t->due_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No tickets yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $tickets->links() }}</div>
        </div>
    </div>
</x-app-layout>