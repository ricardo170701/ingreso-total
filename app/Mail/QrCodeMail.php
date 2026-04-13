<?php

namespace App\Mail;

use App\Support\QrPngGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class QrCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $userName,
        public string $qrToken,
        public string $qrSvg,
        public ?string $expiresAt,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu código QR de acceso — Gobernación del Meta',
        );
    }

    public function content(): Content
    {
        // El PNG no puede guardarse en propiedades del Mailable si va en cola: al serializar a JSON
        // (database/redis) los bytes del PNG rompen UTF-8. Se genera solo al renderizar el correo.
        $qrPngBinary = '';
        try {
            if ($this->qrToken !== '') {
                $qrPngBinary = QrPngGenerator::fromPayload($this->qrToken);
            }
        } catch (Throwable $e) {
            Log::warning('No se pudo generar PNG del QR para el correo: ' . $e->getMessage());
        }

        $logo = public_path('images/logo-gobernacion-meta.png');
        $logoAbsolutePath = is_file($logo) ? $logo : null;

        return new Content(
            view: 'emails.qr-code',
            with: [
                'userName' => $this->userName,
                'qrToken' => $this->qrToken,
                'qrSvg' => $this->qrSvg,
                'expiresAt' => $this->expiresAt,
                'qrPngBinary' => $qrPngBinary,
                'logoAbsolutePath' => $logoAbsolutePath,
            ],
        );
    }

    /**
     * Sin adjuntos: el QR va incrustado en el cuerpo (CID).
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
