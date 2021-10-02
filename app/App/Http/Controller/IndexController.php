<?php

declare(strict_types=1);

namespace Karting\App\Http\Controller;

use Illuminate\View\View;
use Karting\App\Http\Controller;

class IndexController extends Controller
{
    public function index(): View
    {
        return view('index');
    }
}
