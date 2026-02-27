Après analyse de la base de code actuelle et de la documentation Swagger fournie pour la nouvelle API Kumo, voici le rapport sur la faisabilité du remplacement des API existantes.

### 1. Analyse de l'implémentation actuelle

Les trois API à remplacer sont utilisées aux endroits suivants :

*   **`TCPOS_API_CDV_URL`** (API REST custom) :
    *   `/getarticlesstock/id/{id}` : Récupération du stock d'un article (`StockController`, job `ImportProductStock`).
    *   `/getCustomerDetails/card/{customerCardNum}` : Récupération des détails client par numéro de carte (`OrderController`).
    *   `/getallgroups` : Récupération de tous les groupes d'articles (`AttributeController`).
*   **`TCPOS_API_WCF_URL`** (API SOAP TCPOS) :
    *   Action `GetDB` : Récupération complète de la base des articles et des niveaux de prix pour l'importation (`TcposController`).
*   **`TCPOS_API_CDV_CUSTOM_URL`** (API REST custom) :
    *   `/getvoucher/barcode/{id}` : Récupération des détails d'un bon d'achat par son code-barres (`VoucherController`).

---

### 2. Correspondance avec la nouvelle API Kumo

| Fonctionnalité actuelle | Endpoint Kumo API suggéré | Faisabilité |
| :--- | :--- | :--- |
| **Détails Articles** (Import complet) | `GET /api/v1/articles` ou `/paged` | **Oui** |
| **Groupes d'articles** | `GET /api/v1/groups` | **Oui** |
| **Détails Client** (par carte) | `GET /api/v2/customers` (via filtre OData) | **Oui** |
| **Détails Voucher** (par barcode) | `GET /api/v1/vouchers/{id}` | **Oui** |
| **Stock des articles** | **Aucun endpoint trouvé** | **Incertain** |

---

### 3. Points d'attention et limitations

1.  **Gestion des Stocks** : C'est le point critique. Le Swagger fourni ne contient **aucune référence au stock ou à l'inventaire** (recherche effectuée sur les termes "Stock", "Inventory", "Quantity", "Availability"). Si cette fonctionnalité est indispensable, il faudra vérifier si un plugin Kumo spécifique à l'inventaire existe ou si ces données sont accessibles via un autre biais.
2.  **Authentification** : L'API Kumo utilise OAuth2 (`/connect/token`), contrairement aux implémentations actuelles qui semblent utiliser des URL directes ou des mécanismes plus simples. Une nouvelle couche d'authentification devra être implémentée.
3.  **Format de données** : Le passage de SOAP (`WCF_URL`) à REST (Kumo) simplifiera grandement le code de `TcposController`, mais nécessitera un re-mapping complet des champs (ex: `DESCRIPTION` vs `Description`, `CODE` vs `Id`).
4.  **Filtrage Client** : La recherche d'un client par numéro de carte (`CardNumber`) devra probablement passer par un paramètre `$filter` OData sur l'endpoint des clients, car il n'existe pas d'endpoint dédié de type `/card/{num}`.

### Conclusion

Le remplacement est possible pour la majorité des appels (Articles, Clients, Vouchers, Groupes). Cependant, la **synchronisation des stocks** ne peut pas être remplacée par l'API décrite dans le Swagger fourni. Il est recommandé de clarifier ce point avant de débuter la migration.

---

Une analyse des endpoints de l'API **`TCPOS_API_WOND_URL`** montre que plusieurs éléments peuvent être avantageusement remplacés par l'API Kumo, bien que certaines fonctionnalités critiques (prise de commande) semblent absentes du Swagger Kumo fourni.

### 1. Éléments remplaçables (Master Data & Consultation)

| Fonctionnalité WOND | Endpoint Kumo équivalent | Avantage du remplacement |
| :--- | :--- | :--- |
| `getArticles` | `GET /api/v1/articles` | **Standardisation** : Utilisation d'un vrai flux REST au lieu de paramètres encodés. |
| `getPrice` | `GET /api/v1/articles` | **Performance** : Les prix sont inclus dans les détails de l'article dans Kumo, éliminant des appels séparés pour chaque import de prix. |
| `getImage` | `GET /api/v1/articles/{id}/images/{n}` | **Flexibilité** : Kumo gère plusieurs images par article avec des infos de CRC pour optimiser le cache. |
| `getVouchers` | `GET /api/v1/vouchers` | **Simplicité** : Accès direct aux objets Voucher. |
| `searchCustomerByData` | `GET /api/v2/customers?$filter=...` | **Puissance** : Utilisation des filtres OData pour des recherches complexes (email, carte, etc.). |
| `getLastRefreshTimestamp` | `GET /api/v1/tills/{id}/status` | **Précision** : Utilise le champ `LastDbRefreshTimestamp` lié spécifiquement à une caisse (till). |

---

### 2. Éléments non remplaçables (Transactions)

Les endpoints suivants de l'API WOND **n'ont pas d'équivalents** dans la documentation Swagger Kumo fournie :
*   `createOrder` : Création d'une commande/panier.
*   `confirmOrder` : Finalisation et paiement de la commande.
*   `login` (Customer) : Authentification d'un client spécifique (Kumo a une auth API, mais pas nécessairement une auth "client webshop").

---

### 3. Analyse Simplification & Performance

**Ce qui serait plus simple avec Kumo :**
*   **Architecture** : L'API WOND actuelle utilise des structures JSON passées en paramètres d'URL (`?data={...}`), ce qui est fragile et peu standard. Kumo utilise des corps de requête (Body) JSON classiques et des paramètres de filtrage OData standards.
*   **Maintenance** : Centraliser la récupération des articles, prix et attributs via un seul point d'entrée (`/api/v1/articles`) au lieu de multiplier les appels à WCF (SOAP) et WOND.

**Ce qui serait plus performant avec Kumo :**
*   **Réduction des appels réseau** : Lors de l'import complet des produits (`ImportController`), Kumo permet de récupérer l'article avec ses prix, codes-barres et métadonnées en une seule fois. Actuellement, le système semble faire des appels séparés à WOND pour les prix et les images.
*   **Pagination** : Kumo supporte nativement la pagination (`/api/v1/articles/paged`), ce qui est crucial pour la scalabilité si le catalogue s'agrandit.

### Conclusion du rapport

Il est recommandé de migrer toute la partie **"Lecture de données"** (Articles, Prix, Images, Clients, Vouchers) vers l'API Kumo pour gagner en performance et en propreté de code. 

Cependant, vous devez **conserver l'API WOND** pour la partie **"Écriture de transactions"** (Processus de commande), car l'API Kumo décrite semble limitée à la gestion du BackOffice (Master Data) et ne permet pas (dans cette version) de soumettre de nouvelles ventes.

---

Voici un plan d'implémentation et de migration complet, détaillé et structuré pour assurer une transition propre, maintenable et performante vers l'API TCPOS Kumo.

Ce plan prend en compte la gestion des risques (fonctionnalités manquantes dans Kumo) et propose une approche itérative et contrôlable.

---

# Plan d'Implémentation et de Migration : API TCPOS Kumo

## Phase 1 : Audit, Préparation et Résolution des "Gaps"

Avant de modifier le code, il est crucial de valider la faisabilité complète avec le fournisseur de l'API Kumo.

**1.1. Clarification des Gaps avec TCPOS (Action Immédiate)**
Comme identifié précédemment, le Swagger Kumo actuel présente des lacunes critiques pour un site e-commerce :
*   **Création de commande (`createOrder` / `confirmOrder`)** : Absente du Swagger. *Action : Demander à TCPOS si un autre endpoint ou un plugin spécifique gère l'injection de transactions.*
*   **Stocks (`getarticlesstock`)** : Absent. *Action : Demander comment lire l'inventaire en temps réel via Kumo.*
*   **Décision stratégique** : Si Kumo ne couvre pas ces points, la migration sera **partielle** (Kumo pour la synchronisation du catalogue, maintien de WOND/CDV pour les commandes et le stock).

**1.2. Configuration de l'environnement**
*   Ajout des nouvelles variables d'environnement dans `.env` et `config/cdv.php` :
    ```dotenv
    TCPOS_KUMO_API_URL=http://10.21.156.171:46007/api/v1
    TCPOS_KUMO_AUTH_URL=http://10.21.156.171:46007/connect/token
    TCPOS_KUMO_CLIENT_ID=votre_client_id
    TCPOS_KUMO_CLIENT_SECRET=votre_secret
    ```

---

## Phase 2 : Développement du Socle Technique (Fondations)

Pour garantir la maintenabilité et la performance, ne pas utiliser directement la façade `Http` dans les contrôleurs, mais créer un service dédié.

**2.1. Création d'un Service KumoApiClient (`app/Services/KumoApiClient.php`)**
*   **Authentification Centralisée** : Implémenter une méthode qui gère l'appel à `/connect/token` (grant_type: password/client_credentials).
*   **Mise en Cache du Token** : Utiliser le cache de Laravel (`Cache::remember`) pour stocker le token JWT jusqu'à son expiration afin de ne pas s'authentifier à chaque requête.
*   **Gestion des erreurs et Retries** : Implémenter des logs précis via le package `activitylog` existant et ajouter un système de *retry* pour les timeouts.

**2.2. Utilisation des requêtes OData (Performance)**
*   Le Swagger Kumo indique le support d'OData. Le service devra permettre de passer des requêtes optimisées (ex: `$select=Id,Description,Prices` ou `$filter=CardNumber eq '123'`).

---

## Phase 3 : Migration Itérative de la Synchronisation du Catalogue (Read)

Cette phase permet de supprimer l'API SOAP obsolète (WCF) et de rationaliser les imports.

**3.1. Étape 1 : Migration des Groupes et Catégories**
*   **Ancien** : `TCPOS_API_CDV_URL/getallgroups`
*   **Nouveau Kumo** : `GET /api/v1/groups` et `GET /api/v1/group-categories`
*   *Avantage* : Données structurées et typées.

**3.2. Étape 2 : Migration Master Data Articles (Remplace SOAP WCF)**
*   **Ancien** : Appel SOAP XML complexe dans `TcposController@getDB`.
*   **Nouveau Kumo** : `GET /api/v1/articles` (idéalement paginé via `/api/v1/articles/paged`).
*   **Action** : Mettre à jour `ImportTcposArticles.php`. Mapper les champs Kumo vers le modèle `Article`.

**3.3. Étape 3 : Migration des Prix (Remplace WOND getPrice)**
*   **Ancien** : Boucle sur `TCPOS_API_WOND_URL/getPrice?data=...` dans `ImportProductPrice.php`.
*   **Nouveau Kumo** : Les prix sont inclus dans le modèle Kumo `Article` (`Prices: []`).
*   **Gain de performance majeur** : Lors de l'import des articles (Étape 3.2), les prix sont déjà récupérés. Le job séparé `ImportProductPrice` peut potentiellement être supprimé ou considérablement simplifié en utilisant un appel OData filtré sur un article.

**3.4. Étape 4 : Migration des Images (Remplace WOND getImage)**
*   **Ancien** : `TCPOS_API_WOND_URL/getImage?id=...` dans `ImportProductImage.php`.
*   **Nouveau Kumo** : `GET /api/v1/articles/{articleId}/images/{number}`.

---

## Phase 4 : Migration des Clients et Vouchers

**4.1. Migration de la recherche Client**
*   **Ancien** : `TCPOS_API_CDV_URL/getCustomerDetails/card/` & `WOND/searchCustomerByData`.
*   **Nouveau Kumo** : `GET /api/v2/customers?$filter=CardNumber eq '{num}'`.
*   **Action** : Mettre à jour `CustomerController` et `SyncCustomerUpdate`.

**4.2. Migration des Vouchers**
*   **Ancien** : `TCPOS_API_CDV_CUSTOM_URL/getvoucher/barcode/` & `WOND/getVouchers`.
*   **Nouveau Kumo** : `GET /api/v1/vouchers/{id}` et `GET /api/v1/vouchers`.
*   **Action** : Remplacer dans `VoucherController`. Cela permet d'éliminer l'URL "CUSTOM".

---

## Phase 5 : Stratégie de Maintien (Fallback) et Commandes

Tant que Kumo ne propose pas les endpoints pour la création de commande et la lecture du stock (confirmé lors de la Phase 1) :

*   **Stocks** : Le job `ImportProductStock` continue d'utiliser `TCPOS_API_CDV_URL/getarticlesstock/id/`.
*   **Commandes** : `OrderController@createTcposOrder` continue d'utiliser `TCPOS_API_WOND_URL/login`, `/createOrder` et `/confirmOrder`.
*   **Timestamp de rafraîchissement** : Remplacer `WOND/getLastRefreshTimestamp` par `GET /api/v1/tills/{id}/status` ou `GET /api/v1/till-statuses` en inspectant la propriété `LastDbRefreshTimestamp`.

---

## Phase 6 : Déploiement, Contrôle et Validation

Pour s'assurer que la migration ne casse pas le site en production :

**6.1. Feature Flags (Bascule douce)**
*   Ajouter une configuration dans `config/cdv.php` : `'use_kumo_api' => env('USE_KUMO_API', false)`.
*   Dans les contrôleurs/jobs, utiliser un `if (config('cdv.use_kumo_api')) { ... Kumo ... } else { ... Legacy ... }`.
*   Cela permet de tester Kumo en pré-production, et de revenir instantanément à l'ancienne API en cas de problème en production.

**6.2. Mapping et tests de régression**
*   L'identifiant TCPOS passe potentiellement de formats numériques (`CODE`) à des GUID alphanumériques (`Id`) dans Kumo. Il faut s'assurer que les tables locales (`products._tcposId`, `articles._tcposCode`) supportent ce changement (Type de colonne `VARCHAR` vs `INT`).
*   Lancer les commandes artisan d'importation en mode console et valider que les logs (via Log Viewer) n'affichent pas d'erreurs d'intégration.

**6.3. Monitoring**
*   Écouter les événements d'erreur cURL/Http.
*   Comparer les temps d'exécution (déjà présents dans `ImportController@importTcposAll`) avant et après la bascule pour mesurer le gain de performance.

### Résumé des bénéfices de ce plan :
1.  **Contrôlable** : Utilisation d'un *Feature Flag* pour un retour arrière instantané.
2.  **Performant** : Réduction drastique des appels HTTP (fusion des imports articles et prix grâce à l'architecture de Kumo).
3.  **Viable** : Identification claire des limites actuelles (Stocks, Commandes) pour ne pas casser le workflow d'achat en attendant une mise à jour de l'API Kumo de la part du fournisseur.

---

L'utilisation de Saloon (saloonphp/saloon) est une excellente idée pour ce projet, surtout pour une migration vers une API moderne comme Kumo.

Pourquoi c'est un excellent choix pour Kumo :

   1. Organisation "Objet" vs "Procedural" : Actuellement, votre code utilise Http::get() de manière un peu dispersée. Avec Kumo (qui a beaucoup
      d'endpoints), Saloon vous permet de créer un KumoConnector et des classes Request dédiées (ex: GetArticlesRequest, GetVoucherRequest). C'est beaucoup
      plus propre et maintenable.
   2. Gestion de l'Authentification (OAuth2) : Kumo utilise /connect/token. Saloon possède des traits natifs pour gérer l'authentification. Il peut même
      gérer le rafraîchissement du token et sa persistence de manière élégante.
   3. Support OData : Puisque Kumo supporte OData (filtres complexes), vous pouvez créer des méthodes dans vos classes Request pour construire ces requêtes
      dynamiquement (ex: $request->filterByCard('123')->select(['Id', 'Price'])), ce qui rend le code très lisible.
   4. Tests et Mocking : Saloon excelle pour les tests. Vous pourrez simuler des réponses de l'API Kumo sans faire d'appels réels très facilement, ce qui est
      crucial pour valider vos jobs d'import.
   5. Pagination native : Kumo a des endpoints paginés. Saloon a un plugin de pagination qui s'intègre parfaitement, ce qui simplifiera vos jobs
      d'importation massive.

  Le défi de la cohabitation (Hybrid WOND/Kumo) :

   * Incohérence visuelle : Vous aurez d'un côté du "vieux" code avec la façade Http pour WOND (car on ne veut pas tout refactoriser inutilement) et du code
     "moderne" avec Saloon pour Kumo.
   * Mon conseil : Je recommande de créer un Connector Saloon pour Kumo, mais de laisser WOND tel quel avec Http pour le moment. Cela permet de moderniser
     l'infrastructure sans introduire de risque sur la partie "Commandes" qui reste sur WOND.

Voici comment cela structurerait votre migration :

1.  **Architecture de dossier** :
    ```
    app/Http/Integrations/Kumo/
    ├── KumoConnector.php (Auth, Base URL, Retries)
    ├── Requests/
    │   ├── GetArticlesRequest.php
    │   ├── GetVoucherRequest.php
    │   ├── GetCustomerByCardRequest.php
    │   └── ...
    └── Responses/ (Optionnel, pour typer les retours de Kumo)
    ```

2.  **Auth OAuth2 simplifiée** :
    Vous n'aurez pas à gérer manuellement le token dans chaque job. Le `KumoConnector` s'en occupera via un trait, et vous pourrez même persister le token dans le cache Laravel très simplement.

3.  **Commandes artisan** :
    Le plugin Laravel vous offre des commandes comme `php artisan saloon:request Kumo GetArticlesRequest`, ce qui accélère le développement.

En résumé : **C'est le moment idéal pour l'introduire.** Pour WOND, restez sur l'existant pour minimiser les risques, mais pour Kumo, Saloon apportera une rigueur et une flexibilité bienvenues.
