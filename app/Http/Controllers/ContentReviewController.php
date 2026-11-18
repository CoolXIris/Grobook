<?php

namespace App\Http\Controllers;

use App\Enums\PublicationStatus;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentReviewController extends Controller
{
    public function index(Request $request): View
    {
        $publications = Publication::where('team_id', $request->user()->team_id)
            ->withStatus(PublicationStatus::Diajukan)
            ->with(['user', 'latestFile'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);

        return view('reviews.content-index', ['publications' => $publications]);
    }
}
