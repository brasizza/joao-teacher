<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/depoimento', [TestimonialController::class, 'create'])->name('testimonials.create');
