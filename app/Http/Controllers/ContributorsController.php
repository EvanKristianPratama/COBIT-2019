<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ContributorsController extends Controller
{
    public function index()
    {
        return Inertia::render('Contributors/Index');
    }
}
