<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::visibleTo($request->user())
            ->with(['customer', 'agent'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', Rule::in(Ticket::CATEGORIES)],
            'priority' => ['required', Rule::in(Ticket::PRIORITIES)],
        ]);

        $ticket = Ticket::create($data + [
            'customer_id' => $request->user()->id,
            // The SLA deadline comes from config/sla.php
            'due_at' => now()->addHours(config('sla.hours.' . $data['priority'])),
        ]);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket created.');
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['customer', 'agent', 'comments.user']);
        $agents = User::where('role', 'agent')->orderBy('name')->get();

        return view('tickets.show', compact('ticket', 'agents'));
    }

    public function comment(Request $request, Ticket $ticket)
    {
        Gate::authorize('comment', $ticket);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $ticket->comments()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);

        return back()->with('status', 'Reply added.');
    }

    public function update(Request $request, Ticket $ticket)
    {
        Gate::authorize('manage', $ticket);

        $data = $request->validate([
            'status' => ['required', Rule::in(Ticket::STATUSES)],
            'agent_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'agent')],
        ]);

        // Only admins may assign or reassign
        if (! $request->user()->isAdmin()) {
            unset($data['agent_id']);
        }

        $data['resolved_at'] = in_array($data['status'], ['resolved', 'closed']) ? now() : null;
        $ticket->update($data);

        return back()->with('status', 'Ticket updated.');
    }
}