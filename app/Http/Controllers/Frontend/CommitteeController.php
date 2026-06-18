<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendCommitteeService;
use Illuminate\View\View;

class CommitteeController extends Controller
{
    public function __invoke(FrontendCommitteeService $committee): View
    {
        return view('frontend.committee', [
            'members' => $committee->publicMembers(),
        ]);
    }
}
