<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendUsefulDirectoryService;
use Illuminate\View\View;

class UsefulDirectoryController extends Controller
{
    public function __invoke(FrontendUsefulDirectoryService $directory): View
    {
        return view('frontend.useful-directory', [
            'groups' => $directory->publicDirectory(),
        ]);
    }
}
