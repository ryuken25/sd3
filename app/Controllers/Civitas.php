<?php

namespace App\Controllers;

class Civitas extends BaseController
{
    public function index(): string
    {
        return view('civitas/index');
    }
}
