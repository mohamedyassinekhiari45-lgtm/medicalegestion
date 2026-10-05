# Diagrammes de Séquence - Gestion Médicale

## Liste des diagrammes

| # | Diagramme | Fichier PlantUML | Description |
|---|-----------|-------------------|-------------|
| 1 | Authentification avec 2FA | `01-authentification.puml` | Login avec/sans 2FA (TOTP), challenge, vérification |
| 2 | Création d'un rendez-vous | `02-creation-rendezvous.puml` | Le réceptionniste planifie un RDV avec contrôle de conflit |
| 3 | Cycle de vie d'une consultation | `03-consultation.puml` | Confirmation RDV → démarrer → constantes/diagnostic → tarifs → terminer |
| 4a | Génération de la facture | `04a-generation-facture.puml` | Le réceptionniste facture une consultation terminée (contrôle doublon, total des prestations) |
| 4b | Paiement et export | `04b-paiement-export.puml` | Le réceptionniste enregistre un paiement, notifie et exporte le PDF |
| 5 | Partage de dossier (propriétaire) | `05-partage-proprietaire.puml` | Médecin A partage ses notes/documents avec Médecin B |
| 6 | Demande d'accès au dossier | `06-demande-acces.puml` | Médecin B demande l'accès aux notes/documents de Médecin A |
| 7 | Acceptation / Refus de partage | `07-acceptation-refus-partage.puml` | Le notifié accepte ou refuse une demande de partage |
| 8 | Cycle de vie d'un document médical | `08-cycle-vie-document-medical.puml` | Dépôt, modification, remplacement de fichier et suppression (propriétaire uniquement) |

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
