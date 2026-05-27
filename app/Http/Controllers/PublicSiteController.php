<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }
}
