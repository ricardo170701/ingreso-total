<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Exception;

class PasswordResetController extends Controller
{
    /**
     * Mostrar formulario de solicitud de recuperación
     */
    public function showForgotPasswordForm(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Enviar email con link de recuperación
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'email.exists' => 'No encontramos una cuenta con ese correo electrónico.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No encontramos una cuenta con ese correo electrónico.',
            ]);
        }

        // Generar token
        $token = Str::random(64);

        // Eliminar tokens anteriores para este email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Insertar nuevo token (válido por 60 minutos)
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Enviar email
        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $request->email]));

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));

            Log::info('Password reset email sent successfully', [
                'email' => $request->email,
                'user_id' => $user->id,
            ]);

            return back()->with('success', 'Te hemos enviado un enlace para restablecer tu contraseña por correo electrónico.');
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            // Error de transporte SMTP (conexión, autenticación, etc.)
            Log::error('SMTP Transport Error - Password Reset Email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'error_code' => method_exists($e, 'getCode') ? $e->getCode() : null,
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'encryption' => config('mail.mailers.smtp.encryption'),
            ]);

            $isProduction = app()->environment('production');

            // Mensajes específicos según el tipo de error
            $errorMessage = 'Error al enviar el correo. ';

            if (
                stripos($e->getMessage(), 'connection') !== false ||
                stripos($e->getMessage(), 'timeout') !== false
            ) {
                $errorMessage .= $isProduction
                    ? 'No se pudo conectar al servidor de correo. Por favor, intenta más tarde.'
                    : 'Error de conexión al servidor SMTP. Verifica HOST y PORT en .env';
            } elseif (
                stripos($e->getMessage(), 'authentication') !== false ||
                stripos($e->getMessage(), 'login') !== false ||
                stripos($e->getMessage(), 'credentials') !== false
            ) {
                $errorMessage .= $isProduction
                    ? 'Error de autenticación. Contacta al administrador.'
                    : 'Error de autenticación SMTP. Verifica USERNAME y PASSWORD en .env';
            } else {
                $errorMessage .= $isProduction
                    ? 'Por favor, intenta más tarde o contacta al administrador.'
                    : 'Error SMTP: ' . $e->getMessage();
            }

            return back()->withErrors([
                'email' => $errorMessage,
            ])->withInput();
        } catch (\Symfony\Component\Mime\Exception\RfcComplianceException $e) {
            // Error de formato de email
            Log::error('Email Format Error - Password Reset', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'El formato del correo electrónico no es válido.',
            ])->withInput();
        } catch (Exception $e) {
            // Error genérico - captura cualquier otra excepción
            Log::error('Unexpected Error - Password Reset Email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = app()->environment('production')
                ? 'Error al enviar el correo. Por favor, intenta de nuevo más tarde. Si el problema persiste, contacta al administrador del sistema.'
                : 'Error inesperado: ' . $e->getMessage() . ' (Clase: ' . get_class($e) . ') - Revisa los logs para más detalles.';

            return back()->withErrors([
                'email' => $errorMessage,
            ])->withInput();
        }
    }

    /**
     * Mostrar formulario de restablecimiento de contraseña
     */
    public function showResetForm(Request $request, string $token): Response|RedirectResponse
    {
        $email = $request->query('email');

        // Verificar que el token existe y es válido
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset || !Hash::check($token, $passwordReset->token)) {
            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'El enlace de recuperación no es válido o ha expirado.']);
        }

        // Verificar que no haya expirado (60 minutos)
        $createdAt = \Carbon\Carbon::parse($passwordReset->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            return redirect()->route('password.forgot')
                ->withErrors(['token' => 'El enlace de recuperación ha expirado. Por favor, solicita uno nuevo.']);
        }

        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Procesar restablecimiento de contraseña
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'password.required' => 'La contraseña es requerida.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors([
                'token' => 'El enlace de recuperación no es válido o ha expirado.',
            ]);
        }

        // Verificar que no haya expirado
        $createdAt = \Carbon\Carbon::parse($passwordReset->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return back()->withErrors([
                'token' => 'El enlace de recuperación ha expirado. Por favor, solicita uno nuevo.',
            ]);
        }

        // Buscar usuario y actualizar contraseña
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No encontramos una cuenta con ese correo electrónico.',
            ]);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        // Eliminar token usado
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('login')
            ->with('success', 'Tu contraseña ha sido restablecida correctamente. Ya puedes iniciar sesión.');
    }
}
