<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'prenom', 'date_naissance', 'email', 'password', 'role', 'telephone', 'statut', 'pending_email', 'email_verification_code', 'avatar', 'google2fa_secret', 'google2fa_enabled', 'google2fa_pending', 'google2fa_pending_secret', 'last_seen_at'
    ];

    protected $hidden = [
        'password', 'remember_token', 'google2fa_secret'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_naissance' => 'date',
        'google2fa_enabled' => 'boolean',
        'google2fa_pending' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function medecinRendezVous()
    {
        return $this->hasMany(RendezVous::class, 'medecin_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'medecin_id');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class, 'genere_par');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'encaisse_par');
    }

    public function specialites()
    {
        return $this->belongsToMany(Specialite::class, 'medecin_specialite', 'user_id', 'specialite_id');
    }

    public function dossierAuthorizations()
    {
        return $this->hasMany(DossierAuthorization::class, 'medecin_id');
    }

    public function dossiersAutorises()
    {
        return $this->belongsToMany(DossierMedical::class, 'dossier_authorizations', 'medecin_id', 'dossier_medical_id');
    }

    public function rdvCreer()
    {
        return $this->hasMany(RendezVous::class, 'cree_par');
    }

    public function estAdmin()
    {
        return $this->role === 'admin';
    }

    public function estMedecin()
    {
        return $this->role === 'medecin';
    }

    public function estReceptionniste()
    {
        return $this->role === 'receptionniste';
    }
}
