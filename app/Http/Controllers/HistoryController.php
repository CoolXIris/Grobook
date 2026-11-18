<?php

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $publications = Publication::visibleTo($request->user())
            ->withStatus(PublicationStatus::Selesai)
            ->with(['user', 'team', 'latestFile'])
            ->latest('completed_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('history.index', ['publications' => $publications]);
    }
}
