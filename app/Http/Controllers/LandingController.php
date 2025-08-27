<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaticPage;

class LandingController extends Controller
{
    public function index() {
        return view('landing.index');
    }

    public function static_page(Request $request, $slug) {

        $content = StaticPage::where('slug', $slug)->first();

        if(!$content) {
            \abort(404);
        }
        return view('landing.static', compact('content'));
    }

}
