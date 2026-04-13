<?php

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
use Throwable;

final class QrPngGenerator
{
    /**
     * PNG del QR usando GD (extensión gd de PHP). No requiere Imagick.
     *
     * @throws Throwable
     */
    public static function fromPayload(string $payload, int $size = 280): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($payload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size($size)
            ->margin(8)
            ->build();

        return $result->getString();
    }
}
