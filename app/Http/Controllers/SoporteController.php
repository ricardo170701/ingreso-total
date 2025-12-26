<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class SoporteController extends Controller
{
    /**
     * Mostrar página de soporte
     */
    public function index(): Response
    {
        return Inertia::render('Soporte/Index');
    }
}
