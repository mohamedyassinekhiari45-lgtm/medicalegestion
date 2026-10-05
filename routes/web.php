<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SpecialiteController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\DossierMedicalController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityLogController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/', [AuthController::class, 'login']);
Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/2fa-challenge', [AuthController::class, 'challenge2fa'])->name('2fa.challenge');
Route::post('/2fa-challenge', [AuthController::class, 'verify2fa'])->name('2fa.verify');
Route::post('/2fa-challenge/cancel', [AuthController::class, 'cancel2faChallenge'])->name('2fa.cancel');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/verify-email', [AuthController::class, 'verifyEmailCode'])->name('profile.verify-email');
    Route::post('/profile/resend-code', [AuthController::class, 'resendVerificationCode'])->name('profile.resend-code');
    Route::post('/profile/cancel-email-change', [AuthController::class, 'cancelEmailChange'])->name('profile.cancel-email');
    Route::post('/profile/avatar/upload', [AuthController::class, 'uploadAvatar'])->name('profile.avatar.upload');
    Route::post('/profile/avatar/delete', [AuthController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::post('/profile/2fa/enable', [AuthController::class, 'enable2fa'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [AuthController::class, 'confirm2fa'])->name('profile.2fa.confirm');
    Route::post('/profile/2fa/cancel-pending', [AuthController::class, 'cancelPending2fa'])->name('profile.2fa.cancel-pending');
    Route::post('/profile/2fa/disable', [AuthController::class, 'disable2fa'])->name('profile.2fa.disable');
    Route::post('/profile/2fa/regenerate', [AuthController::class, 'regenerate2faSecret'])->name('profile.2fa.regenerate');
    Route::get('/profile/2fa/qrcode', [AuthController::class, 'get2faQrCode'])->name('profile.2fa.qrcode');

    // Patients : admin+réceptionniste CRUD, médecin lecture seule
    Route::resource('patients', PatientController::class)->only(['index'])->middleware('role:admin,receptionniste,medecin');
    Route::resource('patients', PatientController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->middleware('role:admin,receptionniste');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show')->middleware('role:admin,receptionniste,medecin');

    // Rendez-vous : réceptionniste CRUD, médecin lecture seule
    Route::get('/rendez-vous', [RendezVousController::class, 'index'])->name('rendez-vous.index')->middleware('role:receptionniste,medecin');
    Route::get('/rendez-vous/create', [RendezVousController::class, 'create'])->name('rendez-vous.create')->middleware('role:receptionniste');
    Route::post('/rendez-vous', [RendezVousController::class, 'store'])->name('rendez-vous.store')->middleware('role:receptionniste');
    Route::get('/rendez-vous/{rendez_vous}', [RendezVousController::class, 'show'])->name('rendez-vous.show')->middleware('role:receptionniste,medecin');
    Route::get('/rendez-vous/{rendez_vous}/edit', [RendezVousController::class, 'edit'])->name('rendez-vous.edit')->middleware('role:receptionniste');
    Route::put('/rendez-vous/{rendez_vous}', [RendezVousController::class, 'update'])->name('rendez-vous.update')->middleware('role:receptionniste');
    Route::delete('/rendez-vous/{rendez_vous}', [RendezVousController::class, 'destroy'])->name('rendez-vous.destroy')->middleware('role:admin,receptionniste');
    Route::post('/rendez-vous/{rendez_vous}/confirm', [RendezVousController::class, 'confirm'])->name('rendez-vous.confirm')->middleware('role:medecin');
    Route::post('/rendez-vous/{rendez_vous}/unconfirm', [RendezVousController::class, 'unconfirm'])->name('rendez-vous.unconfirm')->middleware('role:medecin');
    Route::post('/rendez-vous/{rendez_vous}/cancel', [RendezVousController::class, 'cancelByDoctor'])->name('rendez-vous.cancel')->middleware('role:medecin');
    Route::get('/calendrier', [RendezVousController::class, 'calendar'])->name('rendez-vous.calendar')->middleware('role:receptionniste,medecin');
    Route::get('/api/events', [RendezVousController::class, 'apiEvents'])->name('api.events')->middleware('role:receptionniste,medecin');
    Route::get('/api/disponibilites', [RendezVousController::class, 'apiDisponibilites'])->name('api.disponibilites')->middleware('role:receptionniste,medecin');
    Route::get('/api/patients', [PatientController::class, 'apiSearch'])->name('api.patients')->middleware('role:admin,receptionniste,medecin');
    Route::get('/api/medecins', [RendezVousController::class, 'apiMedecins'])->name('api.medecins')->middleware('role:receptionniste');
    Route::get('/rendez-vous/pdf/export', [RendezVousController::class, 'exportPdf'])->name('rendez-vous.pdf')->middleware('role:receptionniste,medecin');
    Route::get('/rendez-vous/xlsx/export', [RendezVousController::class, 'exportXlsx'])->name('rendez-vous.xlsx')->middleware('role:receptionniste,medecin');

    // Consultations : médecin uniquement
    Route::resource('consultations', ConsultationController::class)->middleware('role:medecin');
    Route::post('/consultations/start', [ConsultationController::class, 'start'])->name('consultations.start')->middleware('role:medecin');


    Route::resource('users', UserController::class)->middleware('role:admin');
    Route::get('/users/create/role/{role}', [UserController::class, 'createWithRole'])->name('users.create-with-role')->middleware('role:admin');
    Route::put('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate')->middleware('role:admin');
    Route::delete('/users/{user}/force', [UserController::class, 'forceDestroy'])->name('users.force-destroy')->middleware('role:admin');
    Route::get('/users/pdf/export', [UserController::class, 'exportPdf'])->name('users.pdf')->middleware('role:admin');
    Route::resource('specialites', SpecialiteController::class)->except(['create', 'show'])->middleware('role:admin');
    Route::get('/specialites/pdf/export', [SpecialiteController::class, 'exportPdf'])->name('specialites.pdf')->middleware('role:admin');
    Route::resource('tarifs', TarifController::class)->except(['create', 'show'])->middleware('role:admin');
    Route::get('/tarifs/pdf/export', [TarifController::class, 'exportPdf'])->name('tarifs.pdf')->middleware('role:admin');

    Route::resource('factures', FactureController::class)->except(['edit', 'update', 'destroy'])->middleware('role:receptionniste');
    Route::post('/factures/{facture}/paiement', [FactureController::class, 'paiement'])->name('factures.paiement')->middleware('role:receptionniste');
    Route::get('/factures/{facture}/pdf', [FactureController::class, 'pdf'])->name('factures.pdf')->middleware('role:receptionniste');
    Route::match(['get', 'post'], '/consultations/{consultation}/facture', [FactureController::class, 'generate'])->name('factures.generate')->middleware('role:receptionniste');

    // Dossiers médicaux : médecin uniquement
    Route::get('/patients/{patient}/dossier', [DossierMedicalController::class, 'show'])->name('dossiers-medicaux.show')->middleware('role:medecin');
    Route::post('/patients/{patient}/dossier/notes', [DossierMedicalController::class, 'updateNotes'])->name('dossiers-medicaux.notes')->middleware('role:medecin');
    Route::post('/patients/{patient}/dossier/documents', [DossierMedicalController::class, 'storeDocument'])->name('dossiers-medicaux.documents.store')->middleware('role:medecin');
    Route::put('/documents-medicaux/{document}', [DossierMedicalController::class, 'updateDocument'])->name('dossiers-medicaux.documents.update')->middleware('role:medecin');
    Route::delete('/documents-medicaux/{document}', [DossierMedicalController::class, 'destroyDocument'])->name('dossiers-medicaux.documents.destroy')->middleware('role:medecin');

    // Autorisations d'accès au dossier médical
    // l'accès est toujours demandé par celui qui le souhaite, puis accordé
    // par le médecin propriétaire de la section : il n'y a pas de partage direct.
    Route::post('/patients/{patient}/dossier/demander-acces', [DossierMedicalController::class, 'requestAccess'])->name('dossiers-medicaux.request-access')->middleware('role:medecin');
    Route::post('/partages/{shareRequest}/accepter', [DossierMedicalController::class, 'acceptShare'])->name('dossiers-medicaux.accept-share')->middleware('role:medecin');
    Route::post('/partages/{shareRequest}/refuser', [DossierMedicalController::class, 'refuseShare'])->name('dossiers-medicaux.refuse-share')->middleware('role:medecin');
      Route::delete('/partages/{shareRequest}/annuler', [DossierMedicalController::class, 'cancelShare'])->name('dossiers-medicaux.cancel-share')->middleware('role:medecin');
    Route::delete('/dossier-authorizations/{authorization}', [DossierMedicalController::class, 'revokeAccess'])->name('dossiers-medicaux.revoke')->middleware('role:medecin,admin');

    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index')->middleware('role:admin');
    Route::get('/statistiques/pdf', [StatistiqueController::class, 'exportPdf'])->name('statistiques.pdf')->middleware('role:admin');
    Route::get('/statistiques/xlsx', [StatistiqueController::class, 'exportXlsx'])->name('statistiques.xlsx')->middleware('role:admin');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');

    // Activity logs (admin only)
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('role:admin,medecin,receptionniste');
    Route::get('/activity-logs/xlsx', [ActivityLogController::class, 'exportXlsx'])->name('activity-logs.xlsx')->middleware('role:admin,medecin,receptionniste');
});
