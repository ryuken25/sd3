<?php

namespace App\Controllers;

class Akademik extends BaseController
{
    public function index(): string
    {
        return view('akademik/index');
    }
}
