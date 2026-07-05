<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityLog;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('2fa:user:id')) {
            return view('auth.2fa-challenge');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Identifiants incorrects.'
            ])->onlyInput('email');
        }

        if (!$user->statut) {
            return back()->withErrors([
                'email' => 'Votre compte est désactivé.'
            ])->onlyInput('email');
        }

        if ($user->google2fa_enabled) {
            session(['2fa:user:id' => $user->id]);
            return redirect()->route('2fa.challenge');
        }

        Auth::login($user);
        $request->session()->regenerate();
        ActivityLog::log('login', 'Connexion de ' . $user->email, null, null);
        return redirect()->intended(route('dashboard'));
    }

    public function challenge2fa()
    {
        if (!session('2fa:user:id')) {
            return redirect()->route('login');
        }
        return view('auth.2fa-challenge');
    }

    public function verify2fa(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $userId = session('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expirée.');
        }

        $user = User::find($userId);
        if (!$user || !$user->google2fa_enabled || !$user->google2fa_secret) {
            return redirect()->route('login')->with('error', '2FA non configuré.');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            return back()->with('error', 'Code 2FA invalide.');
        }

        session()->forget('2fa:user:id');
        Auth::login($user);
        $request->session()->regenerate();
        ActivityLog::log('login', 'Connexion de ' . $user->email . ' (2FA)', null, null);
        return redirect()->intended(route('dashboard'));
    }

    public function cancel2faChallenge()
    {
        session()->forget('2fa:user:id');
        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->update(['last_seen_at' => null]);
        ActivityLog::log('logout', 'Déconnexion de ' . $user->email, null, null);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function profile()
    {
        $user = Auth::user()->load('specialites');
        return view('auth.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'current_password' => 'required|current_password',
                'password' => 'required|string|min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        if ($data['email'] !== $user->email) {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'pending_email' => $data['email'],
                'email_verification_code' => $code,
            ]);
            Mail::to($data['email'])->send(new EmailVerificationMail($code, $data['email']));
            ActivityLog::log('update', 'Demande de changement d\'email vers ' . $data['email'], 'user', $user->id);
            return redirect()->route('profile')->with('success', 'Un code de confirmation a été envoyé à votre nouvelle adresse email.');
        }

        $user->update($data);
        ActivityLog::log('update', 'Profil mis à jour : ' . $user->name, 'user', $user->id);
        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048']);
        $user = Auth::user();
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
        }
        $filename = time() . '_' . $user->id . '.' . $request->file('avatar')->extension();
        $request->file('avatar')->storeAs('public/avatars', $filename);
        $user->update(['avatar' => $filename]);
        return back()->with('success', 'Photo de profil mise à jour.');
    }

    public function deleteAvatar()
    {
        $user = Auth::user();
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
            $user->update(['avatar' => null]);
        }
        ActivityLog::log('update', 'Photo de profil supprimée', 'user', $user->id);
        return back()->with('success', 'Photo de profil supprimée.');
    }

    public function verifyEmailCode(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = Auth::user();

        if (!$user->pending_email || !$user->email_verification_code) {
            return back()->with('error', 'Aucune demande de changement d\'email en attente.');
        }

        if ($request->code !== $user->email_verification_code) {
            return back()->with('error', 'Code de vérification incorrect.');
        }

        $user->update([
            'email' => $user->pending_email,
            'pending_email' => null,
            'email_verification_code' => null,
        ]);

        ActivityLog::log('update', 'Email modifié vers ' . $user->email, 'user', $user->id);
        return redirect()->route('profile')->with('success', 'Adresse email modifiée avec succès.');
    }

    public function resendVerificationCode()
    {
        $user = Auth::user();

        if (!$user->pending_email) {
            return back()->with('error', 'Aucune demande de changement d\'email en attente.');
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update(['email_verification_code' => $code]);
        Mail::to($user->pending_email)->send(new EmailVerificationMail($code, $user->pending_email));

        return back()->with('success', 'Un nouveau code de confirmation a été envoyé.');
    }

    public function cancelEmailChange()
    {
        $user = Auth::user();
        $user->update([
            'pending_email' => null,
            'email_verification_code' => null,
        ]);
        ActivityLog::log('update', 'Changement d\'email annulé', 'user', $user->id);
        return redirect()->route('profile')->with('success', 'Changement d\'email annulé.');
    }

    public function showForgotForm()
    {
        $email = Auth::check() ? Auth::user()->email : old('email');
        return view('auth.forgot-password', compact('email'));
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lien de réinitialisation envoyé par email.',
            ]);
        }

        return back()->with('success', 'Lien de réinitialisation envoyé par email.');
    }

    public function showResetForm($token)
    {
        $record = DB::table('password_resets')->where('token', $token)->first();

        if (!$record) {
            return redirect()->route('password.forgot')
                ->with('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $record->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Token invalide ou expiré.'], 400);
            }
            return back()->withErrors(['email' => 'Lien de réinitialisation invalide.']);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
        }

        return redirect()->route('login')->with('success', 'Mot de passe réinitialisé. Connectez-vous avec votre nouveau mot de passe.');
    }

    public function enable2fa()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->update([
            'google2fa_pending_secret' => $secret,
            'google2fa_pending' => true,
        ]);

        ActivityLog::log('update', '2FA : activation initiée', 'user', $user->id);
        return redirect()->route('profile')->with('success', 'Scannez le QR code avec Google Authenticator puis saisissez le code pour confirmer.');
    }

    public function confirm2fa(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = Auth::user();

        if (!$user->google2fa_pending || !$user->google2fa_pending_secret) {
            return back()->with('error', 'Aucune activation 2FA en attente.');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_pending_secret, $request->code);

        if (!$valid) {
            return back()->with('error', 'Code invalide. Vérifiez que l\'heure de votre appareil est synchronisée.');
        }

        $user->update([
            'google2fa_secret' => $user->google2fa_pending_secret,
            'google2fa_enabled' => true,
            'google2fa_pending' => false,
            'google2fa_pending_secret' => null,
        ]);

        ActivityLog::log('update', '2FA activé', 'user', $user->id);
        return redirect()->route('profile')->with('success', '2FA activé avec succès !');
    }

    public function cancelPending2fa()
    {
        $user = Auth::user();
        $user->update([
            'google2fa_pending' => false,
            'google2fa_pending_secret' => null,
        ]);
        ActivityLog::log('update', '2FA : opération annulée', 'user', $user->id);
        return redirect()->route('profile')->with('success', 'Opération 2FA annulée.');
    }

    public function disable2fa(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = Auth::user();

        if (!$user->google2fa_secret) {
            return back()->with('error', '2FA non configuré.');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if (!$valid) {
            return back()->with('error', 'Code 2FA invalide. Désactivation annulée.');
        }

        $user->update([
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'google2fa_pending' => false,
            'google2fa_pending_secret' => null,
        ]);

        ActivityLog::log('update', '2FA désactivé', 'user', $user->id);
        return redirect()->route('profile')->with('success', '2FA désactivé avec succès.');
    }

    public function get2faQrCode()
    {
        $user = Auth::user();

        $secret = $user->google2fa_pending ? $user->google2fa_pending_secret : $user->google2fa_secret;

        if (!$secret) {
            return response()->json(['error' => '2FA non configuré.'], 400);
        }

        $google2fa = new Google2FA();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            'Gestion Médicale',
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($qrCodeUrl);
        $base64 = base64_encode($svg);

        return response()->json([
            'qr_code' => 'data:image/svg+xml;base64,' . $base64,
            'secret' => $secret,
        ]);
    }

    public function regenerate2faSecret()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->update([
            'google2fa_pending_secret' => $secret,
            'google2fa_pending' => true,
        ]);

        ActivityLog::log('update', '2FA : régénération initiée', 'user', $user->id);
        return redirect()->route('profile')->with('success', 'Nouvelle clé 2FA générée. Scannez le QR code avec Google Authenticator puis saisissez le code pour confirmer.');
    }
}
