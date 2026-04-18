<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('etudiant.dashboard');
    }
}