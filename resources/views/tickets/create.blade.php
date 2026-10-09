:
@use('App\Models\Ticket')

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">New ticket</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('tickets.store') }}" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf

                <div>
                    <x-input-label for="subject" value="Subject" />
                    <x-text-input id="subject" name="subject" class="mt-1 block w-full" :value="old('subject')" required />
                    <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="category" value="Category" />
                        <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                            @foreach (Ticket::CATEGORIES as $c)
                                <option value="{{ $c }}" @selected(old('category') === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="priority" value="Priority" />
                        <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                            @foreach (Ticket::PRIORITIES as $p)
                                <option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="6" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>

                <x-primary-button>Submit ticket</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>