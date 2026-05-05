<?php

namespace App\Controllers;

class Kebijakan extends BaseController
{
    public function index(): string
    {
        return view('kebijakan/index');
    }
}
