<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function index(){
        // Fetch active locations from DB so admin changes are reflected on public site
        $cities = Location::where('is_active', true)->orderBy('city')->get();
        return view("location", compact("cities"));
    }
}
