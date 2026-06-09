<?php

namespace Database\Seeders;

use App\Models\CollegeDept;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $programmes = [
            ['prog_code' => '100', 'programme' => 'Computer Science', 'department' => 'Computer Science', 'college' => 'College of Natural and Applied Sciences'],
            ['prog_code' => '101', 'programme' => 'Biochemistry', 'department' => 'Biochemistry', 'college' => 'College of Natural and Applied Sciences'],
            ['prog_code' => '200', 'programme' => 'Accounting', 'department' => 'Accounting', 'college' => 'College of Management Sciences'],
            ['prog_code' => '300', 'programme' => 'Law', 'department' => 'Law', 'college' => 'College of Law'],
        ];

        foreach ($programmes as $prog) {
            CollegeDept::firstOrCreate(
                ['prog_code' => $prog['prog_code']],
                $prog
            );
        }

        $students = [
            ['matric_number' => 'RUN/2020/0001', 'SURNAME' => 'ADEYEMI', 'FIRSTNAME' => 'JOHN', 'EMAIL1' => 'john.adeyemi@test.com', 'prog_code' => '100', 'status' => 'active', 'sex' => 'Male', 'session_admitted' => '2020/2021'],
            ['matric_number' => 'RUN/2020/0002', 'SURNAME' => 'OKAFOR', 'FIRSTNAME' => 'GRACE', 'EMAIL1' => 'grace.okafor@test.com', 'prog_code' => '101', 'status' => 'active', 'sex' => 'Female', 'session_admitted' => '2020/2021'],
            ['matric_number' => 'RUN/2020/0003', 'SURNAME' => 'IBRAHIM', 'FIRSTNAME' => 'MOHAMMED', 'EMAIL1' => 'mohammed.ibrahim@test.com', 'prog_code' => '200', 'status' => 'active', 'sex' => 'Male', 'session_admitted' => '2020/2021'],
        ];

        foreach ($students as $student) {
            Student::firstOrCreate(
                ['matric_number' => $student['matric_number']],
                $student
            );
        }
    }
}
