@use('App\Models\Ticket')

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-semibold text-gray-800">{{ $ticket->code }} &middot; {{ $ticket->subject }}</h2>
            <x-badge :type="$ticket->statusColor()">{{ str($ticket->status)->replace('_', ' ')->title() }}</x-badge>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">

            <div class="space-y-6 lg:col-span-2">
                <x-flash />

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="whitespace-pre-line text-gray-800">{{ $ticket->description }}</p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-4 font-semibold text-gray-800">Conversation</h3>

                    @forelse ($ticket->comments as $c)
                        <div class="mb-4 border-b border-gray-100 pb-4">
                            <p class="text-xs text-gray-500">{{ $c->user->name }} ({{ str($c->user->role)->replace('_', ' ') }}) &middot; {{ $c->created_at->diffForHumans() }}</p>
                            <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $c->body }}</p>
                        </div>
                    @empty
                        <p class="mb-4 text-sm text-gray-500">No replies yet.</p>
                    @endforelse

                    <form method="POST" action="{{ route('tickets.comment', $ticket) }}" class="space-y-2">
                        @csrf
                        <textarea name="body" rows="3" class="block w-full rounded-md border-gray-300 text-sm" placeholder="Write a reply..." required></textarea>
                        <x-input-error :messages="$errors->get('body')" />
                        <x-primary-button>Reply</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-lg bg-white p-6 text-sm shadow-sm">
                    <dl class="space-y-3">
                        <div><dt class="text-gray-500">Customer</dt><dd>{{ $ticket->customer->name }}</dd></div>
                        <div><dt class="text-gray-500">Assigned agent</dt><dd>{{ $ticket->agent?->name ?? 'Unassigned' }}</dd></div>
                        <div><dt class="text-gray-500">Category</dt><dd>{{ $ticket->category }}</dd></div>
                        <div><dt class="text-gray-500">Priority</dt><dd><x-badge :type="$ticket->priorityColor()">{{ ucfirst($ticket->priority) }}</x-badge></dd></div>
                        <div>
                            <dt class="text-gray-500">SLA deadline</dt>
                            <dd class="{{ $ticket->isBreached() ? 'font-semibold text-red-600' : '' }}">
                                {{ $ticket->due_at->format('d M Y, H:i') }} ({{ $ticket->due_at->diffForHumans() }})
                            </dd>
                        </div>
                        @if ($ticket->escalation_level >= 2 && ! $ticket->isClosed())
                            <div><x-badge type="red">Escalated to admins</x-badge></div>
                        @endif
                    </dl>
                </div>

                @can('manage', $ticket)
                    <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-3 rounded-lg bg-white p-6 text-sm shadow-sm">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                @foreach (Ticket::STATUSES as $s)
                                    <option value="{{ $s }}" @selected($ticket->status === $s)>{{ str($s)->replace('_', ' ')->title() }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if (auth()->user()->isAdmin())
                            <div>
                                <x-input-label for="agent_id" value="Assign to agent" />
                                <select id="agent_id" name="agent_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                    <option value="">Unassigned</option>
                                    @foreach ($agents as $a)
                                        <option value="{{ $a->id }}" @selected($ticket->agent_id == $a->id)>{{ $a->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <x-primary-button>Save changes</x-primary-button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>