<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrToolController extends Controller
{
    public function index(Request $request): Response
    {
        $codigo = (string) $request->query('codigo', '');
        $codigo = trim($codigo);

        $svg = null;
        if ($codigo !== '') {
            // SVG evita depender de GD/Imagick en servidores mínimos.
            $svg = QrCode::format('svg')
                ->size(280)
                ->margin(2)
                ->generate($codigo);
        }

        return Inertia::render('QrTool', [
            'codigo' => $codigo,
            'svg' => $svg,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:2000'],
        ]);

        // Redirigir a GET para permitir compartir URL con el código.
        return redirect()->route('qr.tool', ['codigo' => $data['codigo']]);
    }
}
