<?php

namespace App\Controllers;

class Personal extends BaseController
{
    public function index(): string
    {
        return view('personal/index');
    }
}
