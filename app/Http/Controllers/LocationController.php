<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function index(){
        // Get all available cities from the enum values
        $cities = [
            (object)['city' => 'Laval'],
            (object)['city' => 'Montreal'],
            (object)['city' => 'Ottawa'],
            (object)['city' => 'Gatineau']
        ];
        return view("location", compact("cities"));
    }
}
