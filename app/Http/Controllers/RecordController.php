<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function index(){
        return view('degree_verification.index');
    }
    public function degree_verification(Request $request){
        return "Welcome to Record Home";
    }
    public function transcript_verification(Request $request){
        return "Welcome to Record Home";
    }


}
