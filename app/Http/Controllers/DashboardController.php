<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $base = fn () => Ticket::visibleTo($request->user());
        $active = ['open', 'in_progress'];

        return view('dashboard', [
            'open' => $base()->whereIn('status', $active)->count(),
            'resolved' => $base()->whereIn('status', ['resolved', 'closed'])->count(),
            'breached' => $base()->whereIn('status', $active)->where('due_at', '<', now())->count(),
            'escalated' => $base()->whereIn('status', $active)->where('escalation_level', 2)->count(),
        ]);
    }
}