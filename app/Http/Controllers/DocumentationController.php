<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    public function index()
    {
        $title = 'Flapa API Documentation';
        $base_url = config('app.url'); // Or set manually
        $author = 'Flapa Dev Team';
        $user_id = 13;

        return view('documentation', compact('title', 'base_url', 'author', 'user_id'));
    }
}

