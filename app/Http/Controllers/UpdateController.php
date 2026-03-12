<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class UpdateController extends Controller
{


public function fetchMatricNumbersFromExternal()
{
    
  $count = 0;

DB::connection('external_db')
    ->table('t_student_test')
    ->select('*')
    ->where('matric_number', 'like', 'RUN%')
    ->where('status', 'GRADUATED')
    ->orderBy('matric_number')
    ->chunk(100, function ($students) use (&$count) {
        foreach ($students as $student) {
            $sessions = DB::connection('external_db')
                ->table('registrations')
                ->where('matric_number', $student->matric_number)
                ->distinct()
                ->orderBy('session_id', 'asc')
                ->value('session_id');
             if (count($sessions) > 0) {
                $firstSession = $sessions[0];
                $lastSession = $sessions[count($sessions) - 1];
                DB::table('t_students')
                    ->where('matric_number', $student->matric_number)
                    ->update([
                        'session_admitted'  => $firstSession,
                        'session_graduated' => $lastSession,
                        ]);
             }
           
        }
    });

echo "<br><br><strong>Total Create:</strong> $count";

}










}




