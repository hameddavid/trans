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
                "course_code"=> "ARC 218",
                "course_title"=> "BUILDING SECURITY AND CONTROL",
                "unit"=> 2,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "ARC 212",
                "course_title"=> "MODELLING WORKSHOP",
                "unit"=> 1,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 311",
                "course_title"=> "MATLAB PROGRAMMING I",
                "unit"=> 0,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "STA 301",
                "course_title"=> "STATISTICAL MACHINE LEARNING I",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "STA 302",
                "course_title"=> "STATISTICAL MACHINE LEARNING II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "STA 401",
                "course_title"=> "BIG DATA ANALYTICS AND MANAGEMENT I",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "STA 402",
                "course_title"=> "BIG DATA ANALYTICS AND MANAGEMENT II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "STA 403",
                "course_title"=> "DEEP LEARNING I",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "STA 404",
                "course_title"=> "DEEP LEARNING II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "STA 220",
                "course_title"=> "STATISTICAL COMPUTING II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "MTH 101",
                "course_title"=> "ELEMENTARY MATHEMATICS I",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 102",
                "course_title"=> "ELEMENTARY MATHEMATICS II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 103",
                "course_title"=> "ELEMENTARY MATHEMATICS III",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 107",
                "course_title"=> null,
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 201",
                "course_title"=> "MATHEMATICAL METHODS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 202",
                "course_title"=> "ELEMENTARY DIFFERENTIAL EQUATIONS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 203",
                "course_title"=> "SETS, LOGIC AND ALGEBRA",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 204",
                "course_title"=> "LINEAR ALGEBRA",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 205",
                "course_title"=> "REAL ANALYSIS I",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "MTH 205",
                "course_title"=> "REAL ANALYSIS I",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "MTH 206",
                "course_title"=> "REAL ANALYSIS II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 207",
                "course_title"=> "NUMERICAL ANALYSIS I",
                "unit"=> 3,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "MTH 207",
                "course_title"=> "NUMERICAL ANALYSIS I",
                "unit"=> 3,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "MTH 208",
                "course_title"=> "VECTORIAL MECHANICS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 209",
                "course_title"=> "INTRODUCTORY MATHEMATICAL ANALYSIS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 211",
                "course_title"=> "ABSTRACT ALGEBRA I",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 212",
                "course_title"=> "NUMERICAL COMPUTATION TECHNIQUES",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 213",
                "course_title"=> "METRIC SPACE TOPOLOGY",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 214",
                "course_title"=> "COMPLEX ANALYSIS",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 216",
                "course_title"=> "DYNAMICS OF A RIGID BODY",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 301",
                "course_title"=> "ABSTRACT ALGEBRA I",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 302",
                "course_title"=> "ABSTRACT ALGEBRA II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 303",
                "course_title"=> "METRIC SPACE TOPOLOGY",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 304",
                "course_title"=> "CALCULUS OF SEVERAL REAL VARIABLES",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 305",
                "course_title"=> "ELEMENTARY DIFFERENTIAL EQUATIONS II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 306",
                "course_title"=> "COMPLEX ANALYSIS II",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 306",
                "course_title"=> "COMPLEX ANALYSIS II",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 307",
                "course_title"=> "COMPUTATIONAL TECHNIQUES IN MATHEMATICS",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 307",
                "course_title"=> "COMPUTATIONAL TECHNIQUES IN MATHEMATICS",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 308",
                "course_title"=> "METRIC SPACE TOPOLOGY",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 308",
                "course_title"=> "METRIC SPACE TOPOLOGY",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 310",
                "course_title"=> "INTRODUCTION TO INDUSTRIAL MATHEMATICS",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 310",
                "course_title"=> "INTRODUCTION TO INDUSTRIAL MATHEMATICS",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 312",
                "course_title"=> "NUMERICAL ANALYSIS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 316",
                "course_title"=> "FINANCIAL MATHEMATICS",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 316",
                "course_title"=> "FINANCIAL MATHEMATICS",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 317",
                "course_title"=> "INTRODUCTION TO OPERATIONS RESEARCH",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "THA 357",
                "course_title"=> "AFRICAN FILM",
                "unit"=> 2,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "ARC 214",
                "course_title"=> "GEOGRAPHICAL INFORMATION SYSTEM",
                "unit"=> 1,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "MEE 400",
                "course_title"=> "STUDENT INDUSTRIAL WORK EXPERIENCE SCHEME II",
                "unit"=> 6,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "MEE 403",
                "course_title"=> "APPLIED THERMODYNAMICS AND HEAT TRANSFER",
                "unit"=> 2,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "MEE 405",
                "course_title"=> "APPLIED FLUID MECHANICS AND AERODYNAMICS",
                "unit"=> 3,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "MEE 407",
                "course_title"=> "MANUFACTURING AUTOMATION (CAD/CAM)",
                "unit"=> 3,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "MEE 409",
                "course_title"=> "EXTRACTIVE METALLURGY",
                "unit"=> 2,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "MEE 411",
                "course_title"=> "METROLOGY",
                "unit"=> 3,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "THA 440",
                "course_title"=> "FILM AND VIDEO EDITING=>ADVANCE PRACTICE II",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "GIT 214",
                "course_title"=> "INTRODUCTION TO MACHINE LEARNING II",
                "unit"=> 0,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "MTH 319",
                "course_title"=> "COMPUTATIONAL TECHNIQUES IN MATHEMATICS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 321",
                "course_title"=> "RINGS AND MODULES",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 323",
                "course_title"=> "ABSTRACT ALGEBRA II",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 325",
                "course_title"=> "VECTOR AND TENSOR ANALYSIS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 327",
                "course_title"=> "INTRODUCTION TO MATHEMATICAL MODELLING",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 329",
                "course_title"=> "ANALYTICAL DYNAMICS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 330",
                "course_title"=> "OPERATIONS RESEARCH",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 398",
                "course_title"=> "STUDENTS INDUSTRIAL WORK",
                "unit"=> 6,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "MTH 400",
                "course_title"=> "STUDENTS INDUSTRIAL WORK",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 401",
                "course_title"=> "ORDINARY DIFFERENTIAL AND INTEGRAL EQUATIONS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 402",
                "course_title"=> "PARTIAL DIFFERENTIAL EQUATIONS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 403",
                "course_title"=> "LEBESQUE MEASURE & INTEGRATION",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 403",
                "course_title"=> "LEBESQUE MEASURE & INTEGRATION",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 404",
                "course_title"=> "FUNCTIONAL ANALYSIS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 405",
                "course_title"=> "GENERAL TOPOLOGY",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 407",
                "course_title"=> "LATTICE THEORY AND BOOLEAN ALGEBRAS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 408",
                "course_title"=> "INTRODUCTION TO QUANTUM MECHANICS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 410",
                "course_title"=> "ELECTROMAGNETISM",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 413",
                "course_title"=> "FLUID DYNAMICS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 415",
                "course_title"=> "OPTIMIZATION",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 418",
                "course_title"=> "INTRODUCTION TO MEASURE AND PROBABILITY",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 420",
                "course_title"=> "NUMERICAL ANALYSIS",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 420",
                "course_title"=> "NUMERICAL ANALYSIS",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 420",
                "course_title"=> "NUMERICAL ANALYSIS",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 423",
                "course_title"=> "CURRENT ISSUES IN INDUSTRIAL MATHEMATICS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 425",
                "course_title"=> "CONTROL THEORY & PROJECT MANAGEMENT",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "MTH 498",
                "course_title"=> "FINAL YEAR PROJECT",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "MTH 499",
                "course_title"=> "SIWES",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 101",
                "course_title"=> "PHYSICAL CHEMISTRY I",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 102",
                "course_title"=> "ORGANIC CHEMISTRY I",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 103",
                "course_title"=> "INORGANIC CHEMISTRY I",
                "unit"=> 1,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 104",
                "course_title"=> "INORGANIC CHEMISTRY I",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 191",
                "course_title"=> "EXPERIMENTAL CHEMISTRY I",
                "unit"=> 1,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 192",
                "course_title"=> "EXPERIMENTAL CHEMISTRY II",
                "unit"=> 1,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 201",
                "course_title"=> "PHYSICAL CHEMISTRY II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 202",
                "course_title"=> "ORGANIC CHEMISTRY II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 203",
                "course_title"=> "INORGANIC CHEMISTRY II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 204",
                "course_title"=> "INTRODUCTION TO ANALYTICAL CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 204",
                "course_title"=> "INTRODUCTION TO ANALYTICAL CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 205",
                "course_title"=> "BASIC PRINCIPLES OF CHEMICAL PROCESSES AND RESOURCES INVENTORY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 205",
                "course_title"=> "BASIC PRINCIPLES OF CHEMICAL PROCESSES AND RESOURCES INVENTORY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 206",
                "course_title"=> "INDUSTRIAL DRAWING",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "QSV 102",
                "course_title"=> "INTRODUCTION TO QUANTITY SURVEYING II",
                "unit"=> 3,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CHM 207",
                "course_title"=> "ORGANIC CHEMISTRY II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 208",
                "course_title"=> "FOOD CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 208",
                "course_title"=> "FOOD CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 292",
                "course_title"=> "EXPERIMENTAL ORGANIC CHEMISTRY I",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 292",
                "course_title"=> "EXPERIMENTAL ORGANIC CHEMISTRY I",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 301",
                "course_title"=> "PHYSICAL CHEMISTRY III",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 302",
                "course_title"=> "ORGANIC CHEMISTRY III",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 303",
                "course_title"=> "INORGANIC CHEMISTRY III",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 304",
                "course_title"=> "POLYMER CHEMISTRY",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CHM 304",
                "course_title"=> "POLYMER CHEMISTRY",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CHM 305",
                "course_title"=> "PETROLEUM CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 306",
                "course_title"=> "ORGANOMETALLIC CHEMISTRY I",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 307",
                "course_title"=> "MACROMOLECULES",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 308",
                "course_title"=> "NATURAL PRODUCTS CHEMISTRY I",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 309",
                "course_title"=> "ORGANIC REACTION MECHANISM",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 310",
                "course_title"=> "TEXTILE CHEMISTRY AND TECHNOLOGY",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CHM 310",
                "course_title"=> "TEXTILE CHEMISTRY AND TECHNOLOGY",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CHM 311",
                "course_title"=> "COLOUR CHEMISTRY AND TECHNOLOGY I",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 312",
                "course_title"=> "INSTRUMENTAL METHODS OF ANALYSIS",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 315",
                "course_title"=> "UNITS OPERATIONS AND HEAT TRANSFER",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 315",
                "course_title"=> "UNITS OPERATIONS AND HEAT TRANSFER",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 315",
                "course_title"=> "UNITS OPERATIONS AND HEAT TRANSFER",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 316",
                "course_title"=> "APPLIED SPECTROSCOPY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 317",
                "course_title"=> "APPLIED SPECTROSCOPY",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 318",
                "course_title"=> "ENVIRONMENTAL CHEMISTRY AND POLLUTION CONTROL",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CHM 318",
                "course_title"=> "ENVIRONMENTAL CHEMISTRY AND POLLUTION CONTROL",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CHM 319",
                "course_title"=> "ENVIRONMENTAL CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20130901"
            ],
            [
                "course_code"=> "CHM 319",
                "course_title"=> "ENVIRONMENTAL CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20130901"
            ],
            [
                "course_code"=> "CHM 321",
                "course_title"=> "USE OF CHEMICAL LITERATURE",
                "unit"=> 1,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 322",
                "course_title"=> "HEAT TRANSFER",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 323",
                "course_title"=> "INTRODUCTION TO CATALYSIS",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 325",
                "course_title"=> "ELEMENT OF FOOD SCIENCE AND TECHNOLOGY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 354",
                "course_title"=> "CHEMICAL PROCESS ENGINEERING",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 354",
                "course_title"=> "CHEMICAL PROCESS ENGINEERING",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 391",
                "course_title"=> "EXPERIMENTAL PHYSICAL CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 392",
                "course_title"=> "EXPERIMENTAL ORGANIC CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 393",
                "course_title"=> "EXPERIMENTAL INORGANIC CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 394",
                "course_title"=> "SIWES",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 400",
                "course_title"=> "SEMINAR",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 400",
                "course_title"=> "SEMINAR",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 402",
                "course_title"=> "THEORY OF ATOMIC & MOLECULAR STRUCTURE",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 402",
                "course_title"=> "THEORY OF ATOMIC & MOLECULAR STRUCTURE",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 403",
                "course_title"=> "GROUP THEORY & SYMMETRY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 406",
                "course_title"=> "ELECTROCHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 406",
                "course_title"=> "ELECTROCHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 406",
                "course_title"=> "ELECTROCHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 407",
                "course_title"=> "CHEMISTRY OF MATERIALS",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 408",
                "course_title"=> "ADVANCED ANALYTICAL CHEMISTRY & APPLICATIONS",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 408",
                "course_title"=> "ADVANCED ANALYTICAL CHEMISTRY & APPLICATIONS",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 409",
                "course_title"=> "POLYMER CHEMISTRY",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 410",
                "course_title"=> "COLOR CHEMISTRY AND TECHNOLOGY II",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 411",
                "course_title"=> "INDUSTRIAL CHEMICAL TECHNOLOGY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 412",
                "course_title"=> "POLYMER TECHNOLOGY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 413",
                "course_title"=> "INDUSTRIAL CHEMICAL PROCESSES",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 415",
                "course_title"=> "PHOTOCHEMISTRY AND PERICYCLIC REACTIONS",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 416",
                "course_title"=> "ORGANIC SYNTHESIS & HETEROCYLIC CHEMISTRY",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 416",
                "course_title"=> "ORGANIC SYNTHESIS & HETEROCYLIC CHEMISTRY",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 417",
                "course_title"=> "APPLIED COLLOID AND SURFACE CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 417",
                "course_title"=> "APPLIED COLLOID AND SURFACE CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 419",
                "course_title"=> "NATURAL PRODUCTS CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 419",
                "course_title"=> "NATURAL PRODUCTS CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CHM 420",
                "course_title"=> "MEDICINAL CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 421",
                "course_title"=> "COORDINATION, LANTHANIDES & ACTINIDES CHEMISTRY",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 421",
                "course_title"=> "COORDINATION, LANTHANIDES & ACTINIDES CHEMISTRY",
                "unit"=> 3,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 422",
                "course_title"=> "NON-AQUEOUS SOLVENTS",
                "unit"=> 1,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 423",
                "course_title"=> "HEAT TRANSFER",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CHM 423",
                "course_title"=> "HEAT TRANSFER",
                "unit"=> 2,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CHM 425",
                "course_title"=> "ORGANOMETALLIC CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 425",
                "course_title"=> "ORGANOMETALLIC CHEMISTRY",
                "unit"=> 2,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 427",
                "course_title"=> "RADIONUCLEAR CHEMISTRY",
                "unit"=> 1,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 428",
                "course_title"=> "QUALITY CONTROL",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 429",
                "course_title"=> "INDUSTRIAL METHODOLOGY",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 430",
                "course_title"=> "INDUSTRIAL MANAGEMENT",
                "unit"=> 2,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 431",
                "course_title"=> "EXPERIMENTAL INDUSTRIAL CHEMISTRY",
                "unit"=> 1,
                "unit_id"=> "20140901"
            ],
            [
                "course_code"=> "CHM 432",
                "course_title"=> "EXPERIMENTAL INDUSTRIAL CHEMISTRY",
                "unit"=> 1,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CHM 498",
                "course_title"=> "PROJECT",
                "unit"=> 6,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CHM 498",
                "course_title"=> "PROJECT",
                "unit"=> 6,
                "unit_id"=> "20090901"
            ],
            [
                "course_code"=> "CHM 499",
                "course_title"=> "PROJECT",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "FMS 101",
                "course_title"=> "MATHEMATICS FOR MANAGEMENT SCIENCES I",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "FMS 102",
                "course_title"=> "MATHEMATICS FOR MANAGEMENT SCIENCES II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "FMS 202",
                "course_title"=> "STATISTICS FOR MANAGEMENT II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "FSS 101",
                "course_title"=> " MATHEMATICS FOR SOCIAL SCIENCES",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "FSS 201",
                "course_title"=> "STATISTICS FOR  SOCIAL SCIENCES",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 413",
                "course_title"=> "HUMAN RESOURCES MANAGEMENT IN TOURISM",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 415",
                "course_title"=> "SAFETY ISSUES AND WORLD CONFLICT CENTRES/SITUATIONS",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 417",
                "course_title"=> "TOURISM SUSTAINABILITY",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 404",
                "course_title"=> "WORLD CULTURAL/SPORTS FESTIVAL AND HOLIDAYS",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 408",
                "course_title"=> "TOUR AND GUIDE OPERATIONS",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 410",
                "course_title"=> "RELIGIOUS TOURISM",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 412",
                "course_title"=> "PUBLIC RELATIONS AND ADVERTISING IN TOURISM",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 414",
                "course_title"=> "FOOD AND CATERING STUDIES",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CSC 371",
                "course_title"=> "WEB DESIGN",
                "unit"=> 2,
                "unit_id"=> "20170901"
            ],
            [
                "course_code"=> "CSC 398",
                "course_title"=> "STUDENT INDUSTRIAL WORK EXPERIENCE SCHEME",
                "unit"=> 6,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 398",
                "course_title"=> "STUDENT INDUSTRIAL WORK EXPERIENCE SCHEME",
                "unit"=> 6,
                "unit_id"=> "20190901"
            ],
            [
                "course_code"=> "CSC 400",
                "course_title"=> "STUDENTS INDUSTRIAL WORK",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 401",
                "course_title"=> "ORGANISATION OF PROGRAMMING LANGUAGES",
                "unit"=> 3,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 402",
                "course_title"=> "PROGRAM DEVELOPMENT METHODS",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 414",
                "course_title"=> "SOFTWARE STUDIO",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 431",
                "course_title"=> "NETWORK SECURITY ISSUES",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 432",
                "course_title"=> "DISTRIBUTED COMPUTING",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 433",
                "course_title"=> "PARALLEL PROCESSING",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 441",
                "course_title"=> "OPERATING SYSTEMS II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 443",
                "course_title"=> "DATABASE SYSTEMS II",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 445",
                "course_title"=> "ARTIFICIAL INTELLIGENCE",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 446",
                "course_title"=> "BASIC SEARCH STRATEGIES",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 449",
                "course_title"=> "DATA COMMUNICATIONS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 451",
                "course_title"=> "OPERATING SYSTEMS II",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 451",
                "course_title"=> "OPERATING SYSTEMS II",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 452",
                "course_title"=> "DESIGN AND ANALYSIS OF ALGORITHMS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 453",
                "course_title"=> "DATABASE SYSTEMS II",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 453",
                "course_title"=> "DATABASE SYSTEMS II",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 456",
                "course_title"=> "PROJECT MANAGEMENT",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 461",
                "course_title"=> "FOUNDATIONS OF HUMAN-COMPUTER INTERACTION",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 471",
                "course_title"=> "WEB DESIGN AND DATA SECURITY",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 472",
                "course_title"=> "MODELING AND SIMULATION",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 473",
                "course_title"=> "SELECTED TOPICS IN COMPUTER SCIENCE",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 473",
                "course_title"=> "SELECTED TOPICS IN COMPUTER SCIENCE",
                "unit"=> 2,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 474",
                "course_title"=> "EXPERT SYSTEMS",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 474",
                "course_title"=> "EXPERT SYSTEMS",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "CSC 475",
                "course_title"=> "AUTOMATA THEORY AND COMPUTABILITY",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "TOS 407",
                "course_title"=> "SIWES",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 403",
                "course_title"=> "TOURISM INFORMATION MANAGEMENT AND MARKETING",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 405",
                "course_title"=> "GLOBAL CONTEMPORARY ISSUES",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 409",
                "course_title"=> "RECREATIONAL/HOSPITALITY PLANNING AND OPERATIONS",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 401",
                "course_title"=> "TOURISM POLICY AND PLANNING I & II",
                "unit"=> 4,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 498",
                "course_title"=> "RESEARCH PROJECT I & II",
                "unit"=> 6,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 402",
                "course_title"=> "SITE SURVEYING AND SITE SELECTION",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "TOS 406",
                "course_title"=> "PARKS, GARDENS AND MONUMENTS MANAGEMENT",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CSC 476",
                "course_title"=> "AUTOMATA THEORY AND COMPUTABILITY",
                "unit"=> 3,
                "unit_id"=> "20110901"
            ],
            [
                "course_code"=> "CSC 476",
                "course_title"=> "AUTOMATA THEORY AND COMPUTABILITY",
                "unit"=> 3,
                "unit_id"=> "20110901"
            ],
            [
                "course_code"=> "CSC 477",
                "course_title"=> "COMPUTER GRAPHICS",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 478",
                "course_title"=> "COMPILER CONSTRUCTION",
                "unit"=> 3,
                "unit_id"=> "20180901"
            ],
            [
                "course_code"=> "CSC 479",
                "course_title"=> "COMPILER CONSTRUCTION",
                "unit"=> 3,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 492",
                "course_title"=> "SELECTED TOPICS IN COMPUTER SCIENCE",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 498",
                "course_title"=> "FINAL YEAR PROJECT",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "CSC 499",
                "course_title"=> "SIWES",
                "unit"=> 6,
                "unit_id"=> "20050901"
            ],
            [
                "course_code"=> "BDG 206",
                "course_title"=> "CONSTRUCTION WORKSHOP PRACTICE II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "BDG 208",
                "course_title"=> "SOIL MECHANICS & FOUNDATION ENGINEERING I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "PST 515",
                "course_title"=> "GENERAL SURGERY AND INTENSIVE CARE",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 308",
                "course_title"=> "QUICKBOOKS II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 313",
                "course_title"=> "CISCO CERTIFIED NETWORK ASSOCIATE (CCNA) I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 413",
                "course_title"=> "MACHINE LEARNING WITH R I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 114",
                "course_title"=> "INTRODUCTION TO PYTHON PROGRAMMING II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 314",
                "course_title"=> "CISCO CERTIFIED NETWORK ASSOCIATE (CCNA) II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 414",
                "course_title"=> "MACHINE LEARNING WITH R II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 104",
                "course_title"=> "PROGRAMMING IN HTMLS WITH JAVASCRIPT, CSS & BOOTSRAP",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 304",
                "course_title"=> "LARAVEL FRAMEWORK, REACT JS & SQL DATABASE",
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
                "course_code"=> "GIT 502",
                "course_title"=> "ISO 37001 ANTI-BRIBERY FOUNDATION & LEAD IMPLEMENTER II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 513",
                "course_title"=> "DIGITAL MARKETING I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 514",
                "course_title"=> "DIGITAL MARKETING II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 315",
                "course_title"=> "ARCHICAD I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 415",
                "course_title"=> "SKETCHUP 3D I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 316",
                "course_title"=> "ARCHICAD II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 416",
                "course_title"=> "SKETCHUP 3D II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 307",
                "course_title"=> "QUICKBOOKS I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "FBE 101",
                "course_title"=> "INTRODUCTION TO ENVIRONMENTAL SCIENCES",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "FBE 201",
                "course_title"=> "ENVIRONMENTAL AND SUSTAINABLE DEVELOPMENT",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 105",
                "course_title"=> "MULTIMEDIA DESIGN USING ADOBE PHOTOSHOP CC I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 106",
                "course_title"=> "MULTIMEDIA DESIGN USING ADOBE PHOTOSHOP CC II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 113",
                "course_title"=> "INTRODUCTION TO PYTHON PROGRAMMING I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 411",
                "course_title"=> "SIMULINK PROGRAMMING I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 511",
                "course_title"=> "PROJECT MANAGEMENT WITH SIMULATIONS I",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 412",
                "course_title"=> "SIMULINK PROGRAMMING II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "GIT 512",
                "course_title"=> "PROJECT MANAGEMENT WITH SIMULATIONS II",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "SOC 423",
                "course_title"=> "ISSUES IN HUMANITARIAN STUDIES",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "PAD 104",
                "course_title"=> "INTRODUCTION TO LOCAL GOVERNMENT",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "PAD 101",
                "course_title"=> "INTRODUCTION TO PUBLIC ADMINISTRATION I",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "MEE 493",
                "course_title"=> "HEAT TRANSFER LABORATORY",
                "unit"=> 1,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 313",
                "course_title"=> "REAL ESTATE LAW I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 311",
                "course_title"=> "ARBITRATION AND AWARDS I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 309",
                "course_title"=> "PRINCIPLES OF TOWN AND COUNTRY PLANNING I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 307",
                "course_title"=> "BUILDING CONSTRUCTION AND MATERIALS I",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 301",
                "course_title"=> "PRINCIPLES OF VALUATION I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 303",
                "course_title"=> "NATIONAL AND LOCAL TAXATION I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 305",
                "course_title"=> "BUILDING MAINTENANCE I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 314",
                "course_title"=> "REAL ESTATELAW II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 300",
                "course_title"=> "STUDENTS WORK EXPERIENCE SCHEME",
                "unit"=> 0,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 316",
                "course_title"=> "SITE ANALYSIS AND MANAGEMENT",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 306",
                "course_title"=> "BUILDING MAINTENANCE II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 312",
                "course_title"=> "ARBITRATION AND AWARDS II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 302",
                "course_title"=> "PRINCIPLES OF VALUATION II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 310",
                "course_title"=> "PRINCIPLES OF TOWN AND COUNTRY PLANNING II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 304",
                "course_title"=> "NATIONAL AND LOCAL TAXATION II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 308",
                "course_title"=> "BUILDING CONSTRUCTION AND MATERIALS II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 320",
                "course_title"=> "BUILDING ECONOMICS",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ESM 318",
                "course_title"=> "NATURAL RESOURCES AND ENVIRONMENTAL PLANNING",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CVE 401",
                "course_title"=> "TECHNICAL REPORT WRITING",
                "unit"=> 2,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "CVE 417",
                "course_title"=> "ENGINEERING ECONOMICS",
                "unit"=> 3,
                "unit_id"=> "20210901"
            ],
            [
                "course_code"=> "CPE 301",
                "course_title"=> "DIGITAL SYSTEM DESIGN WITH VHDL",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CPE 303",
                "course_title"=> "LOW LEVEL LANGUAGE PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CPE 305",
                "course_title"=> "DIGITAL SYSTEM DESIGN LABORATORY",
                "unit"=> 1,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CPE 302",
                "course_title"=> "COMPUTER ORGANIZATION & ARCHITECTURE",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CPE 300",
                "course_title"=> "SIWES I â€“ STUDENTS INDUSTRIAL WORK EXPERIENCE I",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CPE 304",
                "course_title"=> "COMPUTER ENGINEERING LABORATORY",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CPE 306",
                "course_title"=> "INFORMATION SYSTEM ANALYSIS AND DESIGN",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CPE 308",
                "course_title"=> "OPERATING SYSTEM PRINCIPLES",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CPE 310",
                "course_title"=> "OBJECT-ORIENTED PROGRAMMING",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 301",
                "course_title"=> "ARCHITECTURAL DESIGN STUDIO III",
                "unit"=> 4,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 303",
                "course_title"=> "BUILDING MATERIALS, COMPONENTS AND METHODS III",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 305",
                "course_title"=> "THEORY OF ARCHITECTURE I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 309",
                "course_title"=> "ARCHITECTURAL STRUCTURES III",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 307",
                "course_title"=> "LANDSCAPE THEORY AND DESIGN",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 311",
                "course_title"=> "URBAN DESIGN & NEIGHBORHOOD PLANNING",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 317",
                "course_title"=> "BUILDING SERVICES I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 313",
                "course_title"=> "ECONOMICS OF CONSTRUCTION",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 315",
                "course_title"=> "LAW OF BUILDING CONTRACTS & ARBITRATION",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 319",
                "course_title"=> "SOCIOLOGY OF HOUSING",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "ARC 321",
                "course_title"=> "\tPSYCHOLOGY OF PERCEPTION & BEHAVIORAL ARCHITECTURE",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "CSC 421",
                "course_title"=> "NETCENTRIC COMPUTING",
                "unit"=> 2,
                "unit_id"=> "20200901"
            ],
            [
                "course_code"=> "BDG 202",
                "course_title"=> "BUILDING CONSTRUCTION AND MATERIALS II ",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "BDG 204",
                "course_title"=> "STRUCTURAL MECHANICS AND STRENGTH OF MATERIALS II ",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "BDG 201",
                "course_title"=> "BUILDING CONSTRUCTION AND MATERIALS I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "BDG 203",
                "course_title"=> "STRUCTURAL MECHANICS AND STRENGTH OF MATERIALS I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "BDG 205",
                "course_title"=> "CONSTRUCTION WORKSHOP PRACTICE I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "BDG 207",
                "course_title"=> "BUILDING AND ARCHITECTURAL SCIENCE",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "QSV 201",
                "course_title"=> "PRINCIPLES OF MEASUREMENTS AND DESCRIPTION OF BUILDING WORKS  I ",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "QSV 203",
                "course_title"=> "PRINCIPLES OF TENDERING AND ESTIMATING I",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "QSV 202",
                "course_title"=> "PRINCIPLE OF MEASUREMENTS AND DESCRIPTION OF BUILDING WORKS II",
                "unit"=> 3,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "QSV 204",
                "course_title"=> "PRINCIPLES OF TENDERING AND ESTIMATING II",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
            [
                "course_code"=> "URP 110",
                "course_title"=> "SOCIAL ASPECT OF PLANNING",
                "unit"=> 2,
                "unit_id"=> "20220901"
            ],
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
                }
           
        }
       

        // ,
        //     [
        //         "course_code"=> "TOS 411",
        //         "course_title"=> "HOTEL MANAGEMENT",
        //         "unit"=> 2,
        //         "unit_id"=> "20220901"
        //     ]
    }


    

   



     




































}




