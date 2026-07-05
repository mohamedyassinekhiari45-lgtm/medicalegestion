<?php

namespace App\Http\Controllers;

use App\Models\DossierAuthorization;
use App\Models\DossierMedical;
use App\Models\DocumentMedical;
use App\Models\Patient;
use App\Models\ShareRequest;
use App\Models\User;
use App\Notifications\DemandePartage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DossierMedicalController extends Controller
{
    protected function getDossier(Patient $patient)
    {
        return $patient->dossierMedical()->firstOrCreate(
            ['patient_id' => $patient->id],
            ['date_ouverture' => today(), 'notes_generales' => null]
        );
    }

    protected function medecinsTraitants(Patient $patient)
    {
        $userIds = $patient->consultations()->pluck('medecin_id')
            ->merge($patient->rendezVous()->pluck('medecin_id'))
            ->unique();
        return User::whereIn('id', $userIds)->where('role', 'medecin')->get();
    }

    public function show(Patient $patient)
    {
        $user = Auth::user();

        $dossier = $this->getDossier($patient);
        $dossier->load('authorizations.medecin.specialites', 'authorizations.autorisePar.specialites');

        $medecinsAutorisesIds = $dossier->authorizations
            ->where('medecin_id', $user->id)
            ->pluck('autorise_par')
            ->toArray();

        $tousLesDocs = $dossier->documents()->with('uploader')->get();
        $mesDocs = $tousLesDocs->where('uploaded_by', $user->id);
        $docsAutorises = $tousLesDocs->filter(fn($d) =>
            $d->uploaded_by !== $user->id
            && in_array($d->uploaded_by, $medecinsAutorisesIds)
        );
        $autresDocs = $tousLesDocs->reject(fn($d) =>
            $mesDocs->pluck('id')->contains($d->id)
            || $docsAutorises->pluck('id')->contains($d->id)
        );

        $traitants = $this->medecinsTraitants($patient);
        $peutAutoriser = $traitants->where('id', $user->id)->isNotEmpty();

        // All doctors the user can share with (already treated + can grant access)
        $alreadySharedIds = $dossier->authorizations->where('autorise_par', $user->id)->pluck('medecin_id')->toArray();
        $medecinsAutorisables = User::with('specialites')
            ->where('role', 'medecin')
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $alreadySharedIds)
            ->get();

        $medecin = $user;

        // All doctors the user can request access from (traitants who have content + not already authorized)
        $notes = json_decode($dossier->notes_generales ?? '{}', true);
        $lockedMedecinIds = [];
        foreach ($notes as $medId => $note) {
            if ((int)$medId !== (int)$user->id && $note && !in_array((int)$medId, $medecinsAutorisesIds)) {
                $lockedMedecinIds[] = (int)$medId;
            }
        }
        $lockedDocsIds = $autresDocs->pluck('uploaded_by')->unique()->map(fn($v) => (int)$v)->toArray();
        $lockedMedecinIds = array_unique(array_merge($lockedMedecinIds, $lockedDocsIds));

        // Already have access or pending request from me
        $excludeIds = $dossier->authorizations->where('medecin_id', $user->id)->pluck('autorise_par')->map(fn($v) => (int)$v)->toArray();
        $pendingTargetIds = ShareRequest::where('dossier_medical_id', $dossier->id)
            ->where('requester_id', $user->id)
            ->where('statut', 'en_attente')
            ->pluck('from_medecin_id')
            ->map(fn($v) => (int)$v)
            ->toArray();
        $excludeIds = array_unique(array_merge($excludeIds, $pendingTargetIds));

        $requestableMedecins = User::with('specialites')
            ->whereIn('id', $lockedMedecinIds)
            ->whereNotIn('id', $excludeIds)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'label' => 'Dr ' . $m->prenom . ' ' . $m->name,
                'specialites' => $m->specialites->pluck('libelle')->toArray(),
                'specialitesIds' => $m->specialites->pluck('id')->toArray(),
            ])->values();

        $specialites = \App\Models\Specialite::all();

        $shareRequests = ShareRequest::with('fromMedecin.specialites', 'toMedecin.specialites', 'requester')
            ->where('dossier_medical_id', $dossier->id)
            ->where(function($q) use ($user) {
                $q->where('to_medecin_id', $user->id)
                  ->orWhere('from_medecin_id', $user->id);
            })
            ->where('statut', 'en_attente')
            ->get();

        $sentRequests = ShareRequest::with('fromMedecin', 'toMedecin')
            ->where('dossier_medical_id', $dossier->id)
            ->where('requester_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('dossiers-medicaux.show', compact(
            'dossier', 'patient', 'medecin',
            'mesDocs', 'docsAutorises', 'autresDocs', 'medecinsAutorisesIds',
            'traitants', 'peutAutoriser', 'medecinsAutorisables',
            'shareRequests', 'sentRequests', 'requestableMedecins', 'specialites'
        ));
    }

    public function updateNotes(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $dossier = $this->getDossier($patient);
        $notes = json_decode($dossier->notes_generales ?? '{}', true);
        $notes[$user->id] = $request->validate(['notes_generales' => 'nullable|string'])['notes_generales'];
        $dossier->notes_generales = json_encode($notes);
        $dossier->save();

        return back()->with('success', 'Notes enregistrées.');
    }

    public function storeDocument(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'type' => 'required|in:ordonnance,bilan,radio,compte_rendu,autre',
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fichier' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $dossier = $this->getDossier($patient);

        if ($request->hasFile('fichier')) {
            $data['fichier'] = $request->file('fichier')->store('documents_medicaux', 'public');
        }

        $data['dossier_medical_id'] = $dossier->id;
        $data['uploaded_by'] = Auth::id();

        DocumentMedical::create($data);

        return back()->with('success', 'Document ajouté à votre section du dossier.');
    }

    public function destroyDocument(DocumentMedical $document)
    {
        if ($document->uploaded_by !== Auth::id()) {
            return back()->with('error', 'Vous ne pouvez supprimer que vos propres documents.');
        }

        if ($document->fichier) {
            Storage::disk('public')->delete($document->fichier);
        }
        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }

    public function share(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $ownsPatient = $patient->consultations()->where('medecin_id', $user->id)->exists()
            || $patient->rendezVous()->where('medecin_id', $user->id)->exists();
        if (!$ownsPatient) {
            return back()->with('error', 'Vous devez avoir traité ce patient pour partager votre section.');
        }

        $data = $request->validate([
            'to_medecin_id' => 'required|exists:users,id',
            'notes_partagees' => 'nullable|string',
            'documents_partagees' => 'nullable|string',
        ]);

        $notesPartagees = !empty($data['notes_partagees']) ? json_decode($data['notes_partagees'], true) : [];
        $documentsPartagees = !empty($data['documents_partagees']) ? json_decode($data['documents_partagees'], true) : [];

        $medecin = User::findOrFail($data['to_medecin_id']);
        if ($medecin->role !== 'medecin') {
            return back()->with('error', 'Vous ne pouvez partager qu\'avec un médecin.');
        }
        if ((int)$medecin->id === (int)$user->id) {
            return back()->with('error', 'Vous ne pouvez pas partager avec vous-même.');
        }

        $dossier = $this->getDossier($patient);

        // Check existing authorization
        $exists = $dossier->authorizations()->where('medecin_id', $medecin->id)->where('autorise_par', $user->id)->exists();
        if ($exists) {
            return back()->with('error', 'Ce médecin a déjà accès à votre section.');
        }

        // Check pending share request
        $pending = ShareRequest::where('dossier_medical_id', $dossier->id)
            ->where('from_medecin_id', $user->id)
            ->where('to_medecin_id', $medecin->id)
            ->where('statut', 'en_attente')
            ->exists();
        if ($pending) {
            return back()->with('error', 'Une demande est déjà en attente pour ce médecin.');
        }

        $shareRequest = ShareRequest::create([
            'dossier_medical_id' => $dossier->id,
            'from_medecin_id' => $user->id,
            'to_medecin_id' => $medecin->id,
            'requester_id' => $user->id,
            'notes_partagees' => $notesPartagees,
            'documents_partagees' => $documentsPartagees,
            'statut' => 'en_attente',
        ]);

        $medecin->notify(new DemandePartage($shareRequest));

        return back()->with('success', 'Demande de partage envoyée à Dr ' . $medecin->prenom . ' ' . $medecin->name . '.');
    }

    public function requestAccess(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $data = $request->validate(['medecin_id' => 'required|exists:users,id']);

        $medecin = User::findOrFail($data['medecin_id']);
        if ($medecin->role !== 'medecin') {
            return back()->with('error', 'Vous ne pouvez demander l\'accès qu\'à un médecin.');
        }
        if ((int)$medecin->id === (int)$user->id) {
            return back()->with('error', 'Vous ne pouvez pas vous demander l\'accès à vous-même.');
        }

        $dossier = $this->getDossier($patient);

        // Check existing authorization
        $exists = $dossier->authorizations()->where('medecin_id', $user->id)->where('autorise_par', $medecin->id)->exists();
        if ($exists) {
            return back()->with('error', 'Vous avez déjà accès à la section de ce médecin.');
        }

        // Check pending request
        $pending = ShareRequest::where('dossier_medical_id', $dossier->id)
            ->where('from_medecin_id', $medecin->id)
            ->where('to_medecin_id', $user->id)
            ->where('statut', 'en_attente')
            ->exists();
        if ($pending) {
            return back()->with('error', 'Une demande est déjà en attente pour ce médecin.');
        }

        $shareRequest = ShareRequest::create([
            'dossier_medical_id' => $dossier->id,
            'from_medecin_id' => $medecin->id,
            'to_medecin_id' => $user->id,
            'requester_id' => $user->id,
            'notes_partagees' => [],
            'documents_partagees' => [],
            'statut' => 'en_attente',
        ]);

        $medecin->notify(new DemandePartage($shareRequest));

        return back()->with('success', 'Demande d\'accès envoyée à Dr ' . $medecin->prenom . ' ' . $medecin->name . '.');
    }

    public function acceptShare(ShareRequest $shareRequest)
    {
        $user = Auth::user();
        $isOwnerGrant = (int)$shareRequest->from_medecin_id === (int)$user->id;
        $isRecipientAccept = (int)$shareRequest->to_medecin_id === (int)$user->id;

        // Owner can accept if someone requested access; recipient can accept if owner shared
        if (!$isOwnerGrant && !$isRecipientAccept) {
            return back()->with('error', 'Vous n\'êtes pas concerné par cette demande.');
        }
        // If user is the to_medecin_id (recipient of share), only from_medecin_id should accept the request
        // Actually: when owner initiates share -> recipient accepts. When someone requests -> owner accepts.
        // So the acceptor is always the one who is NOT the requester.
        if ((int)$shareRequest->requester_id === (int)$user->id) {
            return back()->with('error', 'Vous ne pouvez pas accepter votre propre demande.');
        }

        if ($shareRequest->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $dossier = $shareRequest->dossierMedical;

        DossierAuthorization::firstOrCreate([
            'dossier_medical_id' => $dossier->id,
            'medecin_id' => $shareRequest->to_medecin_id,
            'autorise_par' => $shareRequest->from_medecin_id,
        ]);

        $shareRequest->update(['statut' => 'acceptee']);

        return redirect()->route('dossiers-medicaux.show', $dossier->patient_id)
            ->with('success', 'Demande de partage acceptée. Dr ' . $shareRequest->toMedecin->prenom . ' ' . $shareRequest->toMedecin->name . ' peut maintenant voir la section de Dr ' . $shareRequest->fromMedecin->prenom . ' ' . $shareRequest->fromMedecin->name . '.');
    }

    public function refuseShare(ShareRequest $shareRequest)
    {
        $user = Auth::user();
        $canRefuse = (int)$shareRequest->from_medecin_id === (int)$user->id
            || (int)$shareRequest->to_medecin_id === (int)$user->id;

        if (!$canRefuse) {
            return back()->with('error', 'Vous n\'êtes pas concerné par cette demande.');
        }
        if ($shareRequest->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $shareRequest->update(['statut' => 'refusee']);

        return redirect()->route('dossiers-medicaux.show', $shareRequest->dossierMedical->patient_id)
            ->with('success', 'Demande de partage refusée.');
    }

    public function grantAccess(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $ownsPatient = $patient->consultations()->where('medecin_id', $user->id)->exists()
            || $patient->rendezVous()->where('medecin_id', $user->id)->exists();
        if (!$ownsPatient) {
            return back()->with('error', 'Vous devez avoir traité ce patient pour autoriser l\'accès à votre section.');
        }

        $data = $request->validate(['medecin_id' => 'required|exists:users,id']);
        $medecin = User::findOrFail($data['medecin_id']);
        if ($medecin->role !== 'medecin') {
            return back()->with('error', 'Vous ne pouvez autoriser qu\'un médecin.');
        }

        $dossier = $this->getDossier($patient);
        $exists = $dossier->authorizations()->where('medecin_id', $medecin->id)->where('autorise_par', $user->id)->exists();
        if ($exists) {
            return back()->with('error', 'Ce médecin a déjà accès à votre section.');
        }

        DossierAuthorization::create([
            'dossier_medical_id' => $dossier->id,
            'medecin_id' => $medecin->id,
            'autorise_par' => $user->id,
        ]);

        return back()->with('success', 'Accès à votre section accordé à Dr ' . $medecin->prenom . ' ' . $medecin->name . '.');
    }

    public function revokeAccess(Request $request, DossierAuthorization $authorization)
    {
        if ($authorization->autorise_par !== Auth::id()) {
            return back()->with('error', 'Vous ne pouvez révoquer que les accès que vous avez accordés.');
        }

        $medecin = $authorization->medecin;
        $authorization->delete();

        return back()->with('success', 'Accès révoqué pour Dr ' . $medecin->prenom . ' ' . $medecin->name . '.');
    }
}
