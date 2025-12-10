<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        if (array_key_exists($locale, config('app.locales', ['en' => 'English', 'id' => 'Indonesian']))) {
            Session::put('locale', $locale);
        }
        return Redirect::back();
    }
}
