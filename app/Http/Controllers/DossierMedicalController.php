<?php

namespace App\Http\Controllers;

use App\Models\DossierAuthorization;
use App\Models\DossierMedical;
use App\Models\DocumentMedical;
use App\Models\Patient;
use App\Models\ShareRequest;
use App\Models\User;
use App\Notifications\DemandeAnnulee;
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

    /**
     * Chaque patient appartient aux medecins qui l'ont consulte (ou qui ont
     * un rendez-vous avec lui). Seul un medecin traitant peut ouvrir le
     * dossier, ecrire ses notes et deposer des documents.
     */
    protected function estMedecinTraitant(Patient $patient): bool
    {
        return $patient->estTraitePar(Auth::id());
    }

    protected function refuserAcces()
    {
        return redirect()->route('patients.index')
            ->with('error', "Ce patient ne fait pas partie de votre file : consultez-le uniquement depuis « Mes patients ».");
    }

    /**
     * Libelle du perimetre demande ou accorde.
     */
    protected function libellePerimetre(bool $notes, bool $documents): string
    {
        if ($notes && $documents) {
            return 'Notes et documents';
        }

        return $notes ? 'Notes seules' : 'Documents seuls';
    }

    public function show(Patient $patient)
    {
        if (!$this->estMedecinTraitant($patient)) {
            return $this->refuserAcces();
        }

        $user = Auth::user();

        $dossier = $this->getDossier($patient);
        $dossier->load('authorizations.medecin.specialites', 'authorizations.autorisePar.specialites');

        $mesAutorisations = $dossier->authorizations->where('medecin_id', $user->id);

        // Le perimetre accorde peut ne couvrir que les notes ou que les documents.
        $medecinsAutorisesNotesIds = $mesAutorisations->where('partage_notes', true)->pluck('autorise_par')->toArray();
        $medecinsAutorisesDocsIds = $mesAutorisations->where('partage_documents', true)->pluck('autorise_par')->toArray();
        $medecinsAutorisesIds = array_values(array_unique(array_merge($medecinsAutorisesNotesIds, $medecinsAutorisesDocsIds)));

        $tousLesDocs = $dossier->documents()->with('uploader')->get();
        $mesDocs = $tousLesDocs->where('uploaded_by', $user->id);
        $docsAutorises = $tousLesDocs->filter(fn($d) =>
            $d->uploaded_by !== $user->id
            && in_array($d->uploaded_by, $medecinsAutorisesDocsIds)
        );
        $autresDocs = $tousLesDocs->reject(fn($d) =>
            $mesDocs->pluck('id')->contains($d->id)
            || $docsAutorises->pluck('id')->contains($d->id)
        );

        $traitants = $this->medecinsTraitants($patient);

        $medecin = $user;

        // On peut demander l'acces a tous les autres medecins traitants du patient,
        // qu'ils aient deja ecrit quelque chose ou non : une section vide peut etre
        // remplie plus tard, et l'autorisation se donne sur la section entiere.
        $traitantIds = $traitants
            ->where('id', '!=', $user->id)
            ->pluck('id')
            ->map(fn($v) => (int)$v)
            ->toArray();

        // On exclut ceux qui m'ont deja autorise et ceux a qui j'ai deja demande.
        $excludeIds = $dossier->authorizations->where('medecin_id', $user->id)->pluck('autorise_par')->map(fn($v) => (int)$v)->toArray();
        $pendingTargetIds = ShareRequest::where('dossier_medical_id', $dossier->id)
            ->where('requester_id', $user->id)
            ->where('statut', 'en_attente')
            ->pluck('from_medecin_id')
            ->map(fn($v) => (int)$v)
            ->toArray();
        $excludeIds = array_unique(array_merge($excludeIds, $pendingTargetIds));

        $requestableMedecins = User::with('specialites')
            ->whereIn('id', $traitantIds)
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
            'mesDocs', 'docsAutorises', 'autresDocs', 'mesAutorisations',
            'medecinsAutorisesIds', 'medecinsAutorisesNotesIds', 'medecinsAutorisesDocsIds',
            'traitants', 'shareRequests', 'sentRequests', 'requestableMedecins', 'specialites'
        ));
    }

    public function updateNotes(Request $request, Patient $patient)
    {
        if (!$this->estMedecinTraitant($patient)) {
            return $this->refuserAcces();
        }

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
        if (!$this->estMedecinTraitant($patient)) {
            return $this->refuserAcces();
        }

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

    /**
     * Un document n'est modifiable que par son auteur, et seulement s'il
     * est toujours le medecin traitant du patient concerne.
     */
    protected function verifierDocument(DocumentMedical $document)
    {
        if ((int)$document->uploaded_by !== (int)Auth::id()) {
            return back()->with('error', 'Vous ne pouvez modifier que vos propres documents.');
        }

        $patient = optional($document->dossierMedical)->patient;
        if (!$patient || !$this->estMedecinTraitant($patient)) {
            return $this->refuserAcces();
        }

        return null;
    }

    public function updateDocument(Request $request, DocumentMedical $document)
    {
        if ($refus = $this->verifierDocument($document)) {
            return $refus;
        }

        $data = $request->validate([
            'type' => 'required|in:ordonnance,bilan,radio,compte_rendu,autre',
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fichier' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $ancienFichier = $document->fichier;
        $nouveauFichier = null;

        if ($request->hasFile('fichier')) {
            try {
                $nouveauFichier = $request->file('fichier')->store('documents_medicaux', 'public');
            } catch (\Throwable $e) {
                return back()->with('error', 'Le fichier n\'a pas pu être enregistré. Le document n\'a pas été modifié.');
            }
            $data['fichier'] = $nouveauFichier;
        }

        try {
            $document->update($data);
        } catch (\Throwable $e) {
            if ($nouveauFichier) {
                Storage::disk('public')->delete($nouveauFichier);
            }
            return back()->with('error', 'Le document n\'a pas pu être modifié.');
        }

        // l'ancien fichier n'est supprime qu'apres la reussite de la mise a jour
        if ($nouveauFichier && $ancienFichier && $ancienFichier !== $nouveauFichier) {
            Storage::disk('public')->delete($ancienFichier);
        }

        return back()->with('success', 'Document modifié.');
    }

    public function destroyDocument(DocumentMedical $document)
    {
        if ((int)$document->uploaded_by !== (int)Auth::id()) {
            return back()->with('error', 'Vous ne pouvez supprimer que vos propres documents.');
        }

        $patient = optional($document->dossierMedical)->patient;
        if (!$patient || !$this->estMedecinTraitant($patient)) {
            return $this->refuserAcces();
        }

        if ($document->fichier) {
            Storage::disk('public')->delete($document->fichier);
        }
        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }

    public function requestAccess(Request $request, Patient $patient)
    {
        if (!$this->estMedecinTraitant($patient)) {
            return $this->refuserAcces();
        }

        $user = Auth::user();
        $data = $request->validate([
            'medecin_id' => 'required|exists:users,id',
            'notes' => 'nullable',
            'documents' => 'nullable',
        ]);

        // Le demandeur choisit ce qu'il veut voir : notes, documents, ou les deux.
        // Le controle explicite protege contre une requete falsifiee (0/0).
        $partageNotes = $request->boolean('notes');
        $partageDocuments = $request->boolean('documents');

        if (!$partageNotes && !$partageDocuments) {
            return back()->withErrors([
                'medecin_id' => 'Sélectionnez au moins un élément : les notes, les documents, ou les deux.',
            ]);
        }

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
            'notes_partagees' => $partageNotes,
            'documents_partagees' => $partageDocuments,
            'statut' => 'en_attente',
        ]);

        $medecin->notify(new DemandePartage($shareRequest));

        $perimetre = $this->libellePerimetre($partageNotes, $partageDocuments);

        return back()->with('success', 'Demande d\'accès aux ' . mb_strtolower($perimetre) . ' du Dr ' . $medecin->prenom . ' ' . $medecin->name . ' envoyée.');
    }

    public function acceptShare(ShareRequest $shareRequest)
    {
        $user = Auth::user();

        // Seul le medecin proprietaire de la section peut autoriser un acces.
        // L acces n'est jamais accorde directement : il fait suite a une demande.
        if ((int)$shareRequest->from_medecin_id !== (int)$user->id) {
            return back()->with('error', 'Seul le médecin propriétaire de la section peut accepter cette demande.');
        }

        if ($shareRequest->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $dossier = $shareRequest->dossierMedical;

        // Le proprietaire accorde exactement ce qui a ete demande.
        $partageNotes = $shareRequest->notes_partagees;
        $partageDocuments = $shareRequest->documents_partagees;

        DossierAuthorization::updateOrCreate([
            'dossier_medical_id' => $dossier->id,
            'medecin_id' => $shareRequest->to_medecin_id,
            'autorise_par' => $shareRequest->from_medecin_id,
        ], [
            'partage_notes' => $partageNotes,
            'partage_documents' => $partageDocuments,
        ]);

        $shareRequest->update(['statut' => 'acceptee']);

        $perimetre = $this->libellePerimetre($partageNotes, $partageDocuments);

        return redirect()->route('dossiers-medicaux.show', $dossier->patient_id)
            ->with('success', 'Accès aux ' . mb_strtolower($perimetre) . ' du Dr ' . $shareRequest->fromMedecin->prenom . ' ' . $shareRequest->fromMedecin->name . ' accordé à Dr ' . $shareRequest->toMedecin->prenom . ' ' . $shareRequest->toMedecin->name . '.');
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

    public function cancelShare(ShareRequest $shareRequest)
    {
        // Seul le demandeur peut annuler sa propre demande, et seulement
        // tant qu'elle n'a pas ete traitee par le proprietaire.
        if ((int)$shareRequest->requester_id !== (int)Auth::id()) {
            return back()->with('error', 'Vous ne pouvez annuler que les demandes que vous avez envoyées.');
        }

        if ($shareRequest->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $shareRequest->update(['statut' => 'annulee']);

        $shareRequest->fromMedecin->notify(new DemandeAnnulee($shareRequest));

        return redirect()->route('dossiers-medicaux.show', $shareRequest->dossierMedical->patient_id)
            ->with('success', 'Demande d\'accès annulée.');
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
