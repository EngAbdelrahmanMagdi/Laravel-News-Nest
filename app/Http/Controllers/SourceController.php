<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Source;

class SourceController extends Controller
{
    public function index()
    {
        return response()->json(Source::all());
    }
}
