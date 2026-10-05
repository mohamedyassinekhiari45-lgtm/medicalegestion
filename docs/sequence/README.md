# Diagrammes de Séquence - Gestion Médicale

## Liste des diagrammes

| # | Diagramme | Fichier PlantUML | Description |
|---|-----------|-------------------|-------------|
| 1 | Authentification avec 2FA | `01-authentification.puml` | Login avec/sans 2FA (TOTP), challenge, vérification |
| 2 | Création d'un rendez-vous | `02-creation-rendezvous.puml` | Le réceptionniste planifie un RDV avec contrôle de conflit |
| 3 | Cycle de vie d'une consultation | `03-consultation.puml` | Confirmation RDV → démarrer → constantes/diagnostic → tarifs → terminer |
| 4a | Génération de la facture | `04a-generation-facture.puml` | Le réceptionniste facture une consultation terminée (contrôle doublon, total des prestations) |
| 4b | Paiement et export | `04b-paiement-export.puml` | Le réceptionniste enregistre un paiement, notifie et exporte le PDF |
| 5 | Accès au dossier (médecin traitant) | `05-acces-medecin-traitant.puml` | Un patient n'est visible que par les médecins qui l'ont consulté ou ont un RDV : fiche, dossier, notes et documents |
| 6 | Demande d'accès au dossier | `06-demande-acces.puml` | Médecin B demande l'accès aux notes/documents de Médecin A (jamais de partage direct) |
| 7 | Acceptation / Refus d'une demande | `07-acceptation-refus-demande.puml` | Seul le propriétaire de la section accepte ou refuse, puis peut révoquer l'accès |
| 8 | Cycle de vie d'un document médical | `08-cycle-vie-document-medical.puml` | Dépôt, modification, remplacement de fichier et suppression (auteur uniquement) |

## Règle d'accès au dossier médical

1. **Patient rattaché à ses médecins traitants** : un médecin accède à un patient s'il l'a
   consulté (`consultations.medecin_id`) ou s'il a un rendez-vous avec lui
   (`rendez_vous.medecin_id`). Sinon il est renvoyé vers la liste « Mes patients ».
2. **Pas de partage direct** : il n'existe plus de case à cocher ni de bouton
   « Partager ». Le partage passe toujours par une demande.
3. **Demande** : le médecin traitant qui veut lire la section d'un collègue envoie une
   demande (`06`).
4. **Décision** : seul le propriétaire de la section accepte ou refuse ; le demandeur ne
   peut pas accepter sa propre demande (`07`).
5. **Réversibilité** : le propriétaire peut révoquer l'accès à tout moment.
6. **Chacun sa section** : un médecin n'écrit que ses propres notes et ne modifie ou
   supprime que ses propres documents ; les sections accordées sont en lecture seule.

## Visualisation

- **PlantUML** : ouvrez les fichiers `.puml` dans VS Code avec extension **PlantUML** (jebbs.plantuml)
- **Alternative** : copiez le contenu d'un fichier `.puml` sur https://www.plantuml.com/plantuml/uml/
- **Serveur local** : si vous avez Docker : `docker run -d -p 8080:8080 plantuml/plantuml-server:jetty`

## Légende des couleurs (recommandée pour les diagrammes)

```plantuml
skinparam actor {
    BorderColor #2c3e50
    FontColor #2c3e50
}
skinparam participant {
    BorderColor #3498db
    BackgroundColor #ecf0f1
}
skinparam arrow {
    Color #2c3e50
}
```
