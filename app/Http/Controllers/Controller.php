<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // type pour en-tête Content-Type des export CSV
    protected const CSV_CONTENT_TYPE = 'text/csv; charset=UTF-8';
}
