<?php

namespace App\Http\Controllers;

use App\Models\LessonExample;
use App\Models\MethodologyStep;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Services\WhatsAppLink;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Render the one-page public site.
     */
    public function __invoke(WhatsAppLink $whatsAppLink): View
    {
        $settings = SiteSetting::current();

        return view('home', [
            'settings' => $settings,
            'methodologySteps' => MethodologyStep::query()->active()->ordered()->get(),
            'lessonExamples' => LessonExample::query()->active()->ordered()->get(),
            'testimonials' => Testimonial::query()->active()->ordered()->get(),
            'whatsAppUrl' => $whatsAppLink->toTeacher($settings),
        ]);
    }
}
