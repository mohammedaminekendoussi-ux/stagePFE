<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('formateur.dashboard');
    }
}