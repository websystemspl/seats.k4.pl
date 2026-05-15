<?php

namespace App\Http\Controllers;

class SettingsController extends Controller
{
    public function index()
    {
        if (auth()->user()->is_admin === 0) {
            return redirect('/');
        }
        return view('settings');
    }
}
