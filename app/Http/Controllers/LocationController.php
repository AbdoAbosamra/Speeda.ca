<?php

namespace App\Http\Controllers;

class LocationController extends Controller
{
    public function index()
    {
        return redirect()
            ->route('service-providers.index')
            ->setStatusCode(301);
    }
}
