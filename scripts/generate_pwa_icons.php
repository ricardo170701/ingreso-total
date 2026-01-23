<?php
/**
 * Genera íconos PWA con fondo verde (#008c3a) + logo blanco centrado.
 *
 * Uso:
 *   php scripts/generate_pwa_icons.php
 *
 * Requiere extensión GD habilitada.
 */
declare(strict_types=1);

function fail(string $msg, int $code = 1): void
{
    fwrite(STDERR, "[generate_pwa_icons] {$msg}\n");
    exit($code);
}

if (!extension_loaded('gd')) {
    fail("La extensión GD no está habilitada en PHP.");
}

$root = dirname(__DIR__);
$srcLogo = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo-gobernacion-meta.png';
$outDir  = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images';

if (!file_exists($srcLogo)) {
    fail("No existe el logo fuente: {$srcLogo}");
}
if (!is_dir($outDir)) {
    fail("No existe el directorio de salida: {$outDir}");
}

/**
 * Convierte píxeles casi-negros en transparentes para el caso
 * en que el PNG tenga fondo negro (evita logo blanco sobre negro).
 */
function makeNearBlackTransparent(\GdImage $img, int $threshold = 18): \GdImage
{
    $w = imagesx($img);
    $h = imagesy($img);

    imagealphablending($img, false);
    imagesavealpha($img, true);

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $rgba = imagecolorat($img, $x, $y);
            $a = ($rgba >> 24) & 0x7F;
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = ($rgba) & 0xFF;

            // Si ya es transparente, no tocar.
            if ($a >= 120) {
                continue;
            }

            if ($r <= $threshold && $g <= $threshold && $b <= $threshold) {
                // Hacer transparente el píxel (mantener bordes suaves: respetar alpha existente)
                $newAlpha = 127;
                $col = imagecolorallocatealpha($img, $r, $g, $b, $newAlpha);
                imagesetpixel($img, $x, $y, $col);
            }
        }
    }

    return $img;
}

function createGreenCanvas(int $size, string $hex = '#008c3a'): \GdImage
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) {
        fail("Color inválido: {$hex}");
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $canvas = imagecreatetruecolor($size, $size);
    if (!$canvas) {
        fail("No se pudo crear el canvas {$size}x{$size}");
    }

    // Fondo sólido (sin alpha) para que Android no lo reemplace por blanco.
    $bg = imagecolorallocate($canvas, $r, $g, $b);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $bg);

    return $canvas;
}

function generateIcon(string $srcLogo, string $outPath, int $size, float $marginRatio = 0.16): void
{
    $logo = imagecreatefrompng($srcLogo);
    if (!$logo) {
        fail("No se pudo abrir el PNG: {$srcLogo}");
    }

    // Asegurar manejo alpha del logo
    imagealphablending($logo, true);
    imagesavealpha($logo, true);
    $logo = makeNearBlackTransparent($logo);

    $lw = imagesx($logo);
    $lh = imagesy($logo);
    if ($lw <= 0 || $lh <= 0) {
        fail("Dimensiones inválidas del logo: {$lw}x{$lh}");
    }

    $canvas = createGreenCanvas($size);

    $safe = (int) round($size * (1 - (2 * $marginRatio)));
    $safe = max(1, $safe);

    // Escalar manteniendo proporción dentro del área segura
    $scale = min($safe / $lw, $safe / $lh);
    $tw = (int) floor($lw * $scale);
    $th = (int) floor($lh * $scale);
    $tw = max(1, $tw);
    $th = max(1, $th);

    $dstX = (int) floor(($size - $tw) / 2);
    $dstY = (int) floor(($size - $th) / 2);

    imagealphablending($canvas, true);
    imagesavealpha($canvas, false);

    $ok = imagecopyresampled($canvas, $logo, $dstX, $dstY, 0, 0, $tw, $th, $lw, $lh);
    if (!$ok) {
        fail("Falló el reescalado del logo para {$outPath}");
    }

    // Guardar PNG
    imagesavealpha($canvas, false);
    $saved = imagepng($canvas, $outPath, 9);
    imagedestroy($logo);
    imagedestroy($canvas);

    if (!$saved) {
        fail("No se pudo escribir el archivo: {$outPath}");
    }
}

$targets = [
    ['file' => $outDir . DIRECTORY_SEPARATOR . 'pwa-icon-192.png', 'size' => 192],
    ['file' => $outDir . DIRECTORY_SEPARATOR . 'pwa-icon-512.png', 'size' => 512],
    ['file' => $outDir . DIRECTORY_SEPARATOR . 'apple-touch-icon-180.png', 'size' => 180],
];

foreach ($targets as $t) {
    generateIcon($srcLogo, $t['file'], $t['size']);
    fwrite(STDOUT, "[generate_pwa_icons] OK: {$t['file']}\n");
}

