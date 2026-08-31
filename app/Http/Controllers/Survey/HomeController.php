<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {

        session()->forget('survey');

        return view('survey.index', [
            'totalResponden' => 1248,
            'nilaiIkm'       => 87.42,
            'mutuPelayanan'  => 'B',
        ]);
    }
}
