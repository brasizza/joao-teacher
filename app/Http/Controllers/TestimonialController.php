<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;

class TestimonialController extends Controller
{
    /**
     * Show the public page where students leave a testimonial. It is not
     * linked from the site: the teacher shares its link with students.
     */
    public function create(): View
    {
        return view('testimonials.create', [
            'settings' => SiteSetting::current(),
        ]);
    }
}
