<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Application;

class AdminController extends Controller
{
    public function viewPendingApplications(Request $request){
        $data = [];
        $apps = Application::where('app_status','10')->select('*')->get(); 
        return view('pages.pending_requests',['data'=>$data,'apps'=>$apps]);
    }
}
