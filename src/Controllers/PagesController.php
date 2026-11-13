<?php

namespace App\Controllers;

class PagesController extends Controller
{
    /**
     * About - About page
     *
     * @return mixed
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Contact - Contact page
     *
     * @return mixed
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Example - example
     *
     * @return void
     */
    public function example()
    {
        echo params()->id;
    }
}
