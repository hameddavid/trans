<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Applicant;
use App\Models\RegistrationResult;
use App\Models\DegreeVerification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;
use PDF;


class MiscController extends Controller
{
    public function load_course_from_local_db_not_online(){

        $data =  [
           
            [
                "course_code"=> "URP 102",
                "course_title"=> "POPULATION AND URBANIZATION STUDIES",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "URP 106",
                "course_title"=> "URBAN DEVELOPMENT PLANNING",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "URP 108",
                "course_title"=> "INTRODUCTION TO ENVIRONMENTAL DESIGN AND MANAGEMENT",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "URP 112",
                "course_title"=> "INTRODUCTION TO GEOMORPHOLOGY",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CVE 411",
                "course_title"=> "STRUCTURAL MECHANICS II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CVE 415",
                "course_title"=> "ENVIRONMENTAL ENGINEERING",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CVE 400",
                "course_title"=> "STUDENT INDUSTRIAL WORK EXPERIENCE SCHEME II (SIWES II)",
                "unit"=> 6,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ACC 103",
                "course_title"=> "ELEMENTS OF GOVERNMENT",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 211",
                "course_title"=> "INTRODUCTION TO TECHNOLOGY AND WORKSHOP PRACTICE",
                "unit"=> 1,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CYB 102",
                "course_title"=> "FUNDAMENTALS OF CYBER SECURITY I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "SEN 104",
                "course_title"=> "INTRODUCTION TO WEB TECHNOLOGIES",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 416",
                "course_title"=> "MANAGEMENT OF TOURISM INDUSTRY",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 319",
                "course_title"=> "TOURISM AND COMMUNITY (RURAL) DEVELOPMENT",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 317",
                "course_title"=> "FOOD MICROBIOLOGY IN HOSPITALITY MANAGEMENT",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 316",
                "course_title"=> "WILDLIFE, ZOO MANAGEMENT ",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 318",
                "course_title"=> "LANDSCAPE PLANNING",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 320",
                "course_title"=> "FOOD SCIENCE AND FOOD COMMODITIES",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 110",
                "course_title"=> "VISITOR ATTRACTIONS AND MANAGEMENT",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 108",
                "course_title"=> "CULTURAL ARTEFACTS",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 210",
                "course_title"=> "ECOTOURISM",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 212",
                "course_title"=> "HOSPITALITY SALES AND MARKETING",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 214",
                "course_title"=> "MENU PLANNING AND CATERING SERVICES",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GEY 201",
                "course_title"=> "PHYSICAL GEOLOGY",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GEY 203",
                "course_title"=> "MINERALOGY",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GEY 205",
                "course_title"=> "INTRODUCTORY GEOLOGICAL MAP INTERPRETATIONS",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GEY 207",
                "course_title"=> "ENGINEERING SURVEYING",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "URP 101",
                "course_title"=> "HISTORY OF TOWN PLANNING",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 212",
                "course_title"=> "MODELLING WORKSHOP",
                "unit"=> 1,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CVE 313",
                "course_title"=> "ELEMENTS OF ARCHITECTURE",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CSC 101",
                "course_title"=> "INTRODUCTION TO COMPUTER SCIENCE",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 102",
                "course_title"=> "INTRODUCTION TO PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20110901"
            ],
            [
                "course_code"=> "CSC 102",
                "course_title"=> "INTRODUCTION TO PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20110901"
            ],
            [
                "course_code"=> "CSC 104",
                "course_title"=> "COMPUTER SYSTEM DESIGN",
                "unit"=> 3,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 104",
                "course_title"=> "COMPUTER SYSTEM DESIGN",
                "unit"=> 3,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 106",
                "course_title"=> "PROFESSIONAL ETHICS AND LEGAL ISSUES IN INFORMATION TECHNOLOGY",
                "unit"=> 2,
                "unit_id"=> "20150901"
            ],
            [
                "course_code"=> "CSC 112",
                "course_title"=> "INTRODUCTION TO COMPUTING",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 201",
                "course_title"=> "MANAGEMENT INFORMATION SYSTEM I",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 202",
                "course_title"=> "MANAGEMENT INFORMATION SYSTEM II",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 202",
                "course_title"=> "MANAGEMENT INFORMATION SYSTEM II",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 204",
                "course_title"=> "INFORMATION THEORY AND CODING",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 204",
                "course_title"=> "INFORMATION THEORY AND CODING",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 211",
                "course_title"=> "COMPUTER PROGRAMMING I",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 212",
                "course_title"=> "OBJECT-ORIENTED PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 213",
                "course_title"=> "COMPUTER PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 213",
                "course_title"=> "COMPUTER PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 214",
                "course_title"=> "SOFTWARE SYSTEM DESIGN",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 215",
                "course_title"=> "BASIC APPLICATION SOFTWARE PACKAGE",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 218",
                "course_title"=> "FOUNDATION OF SEQUENTIAL PROGRAMMING",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 221",
                "course_title"=> "MACHINE & ASSEMBLY LANGUAGE PROGRAMMING",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 222",
                "course_title"=> "COMPUTER HARDWARE AND LOGIC DESIGN",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 222",
                "course_title"=> "COMPUTER HARDWARE AND LOGIC DESIGN",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 223",
                "course_title"=> "COMPUTER HARDWARE SYSTEM",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 223",
                "course_title"=> "COMPUTER HARDWARE SYSTEM",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 224",
                "course_title"=> "LOGIC DESIGN",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 224",
                "course_title"=> "LOGIC DESIGN",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 225",
                "course_title"=> "ASSEMBLY LANGUAGE PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 227",
                "course_title"=> "COMPUTER NETWORKS",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 227",
                "course_title"=> "COMPUTER NETWORKS",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 229",
                "course_title"=> "COMPUTER SYSTEM DESIGN II",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 231",
                "course_title"=> "SOFTWARE PACKAGES",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 233",
                "course_title"=> "SOFTWARE ENGINEERING",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 233",
                "course_title"=> "SOFTWARE ENGINEERING",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 242",
                "course_title"=> "DISCRETE STRUCTURES",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 242",
                "course_title"=> "DISCRETE STRUCTURES",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CSC 244",
                "course_title"=> "DATA STRUCTURES AND ALGORITHMS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 252",
                "course_title"=> "PARALLELISM FUNDAMENTALS",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 254",
                "course_title"=> "DATA STRUCTURES ALGORITHMS",
                "unit"=> 2,
                "unit_id"=> "20160901"
            ],
            [
                "course_code"=> "CSC 301",
                "course_title"=> "STRUCTURED PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 305",
                "course_title"=> "MULTIMEDIA",
                "unit"=> 2,
                "unit_id"=> "20170901"
            ],
            [
                "course_code"=> "CSC 312",
                "course_title"=> "WEB DESIGN",
                "unit"=> 3,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 316",
                "course_title"=> "PROGRAM DEVELOPMENT METHODS",
                "unit"=> 3,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 318",
                "course_title"=> "NETWORK SECURITY ISSUES",
                "unit"=> 3,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 320",
                "course_title"=> "SYSTEM ANALYSIS AND DESIGN",
                "unit"=> 3,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 323",
                "course_title"=> "COMPUTATIONAL SCIENCE AND NUMERICAL METHODS",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 324",
                "course_title"=> "COMPUTER ARCHITECTURE AND ORGANIZATION II",
                "unit"=> 3,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 326",
                "course_title"=> "INTRODUCTION TO THE INTERNET",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 331",
                "course_title"=> "FOUNDATION CONCEPTS IN INFORMATION ASSURANCE AND SECURITY",
                "unit"=> 2,
                "unit_id"=> "20170901"
            ],
            [
                "course_code"=> "CSC 332",
                "course_title"=> "SURVEY OF PROGRAMMING LANGUAGES",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 332",
                "course_title"=> "SURVEY OF PROGRAMMING LANGUAGES",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 333",
                "course_title"=> "FUNDAMENTAL OF SOFTWARE ENGINEERING",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 341",
                "course_title"=> "OPERATING SYSTEMS I",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 343",
                "course_title"=> "DATABASE SYSTEMS I",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 344",
                "course_title"=> "COMPUTER SYSTEM DESIGN",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 345",
                "course_title"=> "COMPUTER ARCHITECTURE AND ORGANISATION I",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 345",
                "course_title"=> "COMPUTER ARCHITECTURE AND ORGANISATION I",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 346",
                "course_title"=> "FORMAL METHODS IN SOFTWARE DEVELOPMENT",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 347",
                "course_title"=> "DATA COMMUNICATIONS",
                "unit"=> 3,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 348",
                "course_title"=> "COMPUTER NETWORKS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 349",
                "course_title"=> "OBJECT ORIENTED PROGRAMMING II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 351",
                "course_title"=> "PARALLEL ALGORITHMS,COMMUNICATION AND COORDINATION",
                "unit"=> 2,
                "unit_id"=> "20170901"
            ],
            [
                "course_code"=> "CSC 356",
                "course_title"=> "DATA STRUCTURES",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 358",
                "course_title"=> "NUMERICAL COMPUTATION TECHNIQUES",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "GIT 315",
                "course_title"=> "ARCHICAD I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 501",
                "course_title"=> "ISO 37001 ANTI-BRIBERY FOUNDATION & LEAD IMPLEMENTER I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CHM 317",
                "course_title"=> "APPLIED SPECTROSCOPY",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "FMS 201",
                "course_title"=> "STATISTICS FOR MANAGEMENT I",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "THA 435",
                "course_title"=> "PRINCIPLES AND METHODS OF SCREEN AND SOUND=>ADVANCE PRACTICE IN SYNCHRONIZATION",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "FSS 102",
                "course_title"=> "MATHEMATICS FOR SOCIAL SCIENCES II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "FSS 202",
                "course_title"=> "BASIC STATISTICS FOR SOCIAL SCIENCES II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 213",
                "course_title"=> "INTRODUCTION TO MACHINE LEARNING I",
                "unit"=> 0,
                "unit_id"=> "20210901"
            ]
            ];

            foreach($data as $key => $arr){
               $Check =  DB::table('t_course')->where('course_code', $arr['course_code'])->first();
                if(!$Check){
                DB::insert('insert into t_course (course_code, course_title,unit,unit_id) values (?, ?,?,?)',[$arr['course_code'],$arr['course_title'],$arr['unit'],$arr['unit_id']]);
                echo $arr['course_code'] ."  Done <br>"; 
            }
           
        }
       

       
    }


    

   



     




































}




