<?php

namespace App\Controllers;

class Keuangan extends BaseController
{
    public function index(): string
    {
        return view('keuangan/index');
    }
}
