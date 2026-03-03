# Plan d'Implémentation et de Migration : API TCPOS Kumo (Saloon)

Ce document détaille les étapes précises pour migrer les intégrations API actuelles vers la nouvelle API TCPOS Kumo en utilisant **Saloon**.

---

## Sommaire
1. [Phase 1 : Environnement et Dépendances](#phase-1)
2. [Phase 2 : Socle Technique (Saloon Foundation)](#phase-2)
3. [Phase 3 : Implémentation des Requêtes Kumo](#phase-3)
4. [Phase 4 : Migration de la Logique Métier (Détails par Contrôleur)](#phase-4)
5. [Phase 5 : Base de Données et Types d'Identifiants](#phase-5)
6. [Phase 6 : Tests et Validation](#phase-6)

---

<a name="phase-1"></a>
## Phase 1 : Environnement et Dépendances

### 1.1 Installation
```bash
composer require saloonphp/saloon saloonphp/laravel-plugin
```

### 1.2 Configuration `.env`
```dotenv
TCPOS_KUMO_BASE_URL="http://10.21.156.171:46007/api/v1"
TCPOS_KUMO_AUTH_URL="http://10.21.156.171:46007/connect/token"
TCPOS_KUMO_USERNAME="your_username"
TCPOS_KUMO_PASSWORD="your_password"
```

### 1.3 Mise à jour de `config/cdv.php`
Ajouter la variables d'environnement dans `config/cdv.php` :

---

<a name="phase-2"></a>
## Phase 2 : Socle Technique (Saloon Foundation)

### 2.1 Création du Connecteur Kumo
Générer le connecteur : `php artisan saloon:connector Kumo KumoConnector`

Fichier : `app/Http/Integrations/Kumo/KumoConnector.php`
- Implémenter `HasAuthenticator`.
- Gérer l'authentification OAuth2 (Grant Type: Password).
- Utiliser le cache Laravel pour le token.

```php
protected function defaultAuth(): TokenAuthenticator
{
    $token = Cache::remember('kumo_api_token', 3600, function () {
        $response = Http::asForm()->post(config('cdv.kumo.auth_url'), [
            'grant_type' => 'password',
            'username' => config('cdv.kumo.username'),
            'password' => config('cdv.kumo.password'),
        ]);
        return $response->json('access_token');
    });
    return new TokenAuthenticator($token);
}
```

---

<a name="phase-3"></a>
## Phase 3 : Implémentation des Requêtes Kumo

| Requête Saloon | Endpoint Kumo | Paramètres OData recommandés |
| :--- | :--- | :--- |
| `GetArticlesRequest` | `/api/v1/articles` | `$expand=Prices,Barcodes` |
| `GetPagedArticlesRequest` | `/api/v1/articles/paged` | Utiliser pour les imports massifs |
| `GetGroupsRequest` | `/api/v1/groups` | Remplace `/getallgroups` |
| `GetStockLevelRequest` | `/api/v1/stock-levels/articles/{id}` | Pour le stock unitaire |
| `GetCustomerByCardRequest`| `/api/v2/customers` | `$filter=CardNumber eq '...'` |
| `GetVoucherRequest` | `/api/v1/vouchers/{id}` | Par barcode/id |
| `GetArticleImageRequest` | `/api/v1/articles/{id}/images/{n}` | Pour les images |
| `GetTillStatusRequest` | `/api/v1/tills/{id}/status` | Pour `LastDbRefreshTimestamp` |

---

<a name="phase-4"></a>
## Phase 4 : Migration de la Logique Métier

### 4.1 `TcposController` (Import Articles)
- **Source actuelle** : SOAP WCF `GetDB`.
- **Nouvelle source** : `GetArticlesRequest` (ou paged).
- **Mapping** :
    - `CODE` (SOAP) -> `Code` (Kumo)
    - `DESCRIPTION` (SOAP) -> `Description` (Kumo)
    - `NOTES-2` (SOAP) -> `Note2` (Kumo)
    - `PRICELEVELS` (SOAP) -> `Prices` (Kumo Collection)

### 4.2 `ProductController` (Import Articles & Prix)
- **Source actuelle** : WOND `/getArticles` et Jobs `ImportProductPrice`.
- **Optimisation** : Kumo inclut les prix dans l'objet Article. On peut fusionner l'import articles et prix en utilisant `$expand=Prices`.
- **Mapping Prix** : Parcourir `Prices[]` de Kumo et mettre à jour la table `prices`.

### 4.3 `AttributeController` (Import Groupes)
- **Source actuelle** : CDV `/getallgroups`.
- **Nouvelle source** : `GetGroupsRequest`.
- **Mapping** : `ID` -> `_tcposId`, `CODE` -> `_tcposCode`, `DESCRIPTION` -> `name`.

### 4.4 `CustomerController` (Données Client)
- **Source actuelle** : WOND `/searchCustomerByData` + `/getCustomer`.
- **Nouvelle source** : `GetCustomerByCardRequest` avec `$filter`.
- **Mapping** : Mapper les champs Kumo (`FirstName`, `LastName`, `Email`, `Address`) vers le format JSON attendu par le frontend.

### 4.5 `StockController` & `ImportProductStock`
- **Source actuelle** : CDV `/getarticlesstock/id/{id}`.
- **Nouvelle source** : `GetStockLevelRequest`.
- **Champ** : `AvailableQuantity`.

### 4.6 `ImportController` (Vérification Refresh)
- **Source actuelle** : WOND `/getLastRefreshTimestamp`.
- **Nouvelle source** : `GetTillStatusRequest`.
- **Champ** : `LastDbRefreshTimestamp`.

---

<a name="phase-5"></a>
## Phase 5 : Base de Données et Types d'Identifiants

**CRITIQUE** : L'API Kumo utilise des GUID (String) pour les `Id`. Les colonnes suivantes doivent être migrées en `string` :

- Table `articles` : `_tcposId`, `groupACode`, `groupBCode`, `groupCCode`, `groupDCode`.
- Table `products` : `_tcposId`, `groupAId`, `groupBId`, `groupCId`, `groupDId`.
- Table `prices` : `_tcpos_product_id`.
- Table `stocks` : `_tcpos_product_id`.
- Table `attributes` : `_tcposId`.

```php
Schema::table('products', function (Blueprint $table) {
    $table->string('_tcposId')->change();
    // ...
});
```

---

<a name="phase-6"></a>
## Phase 6 : Tests et Validation

### 6.1 Protocole de Test
1. Créer `tests/Feature/KumoMigration/ArticleImportTest.php`.
2. Utiliser `Saloon::fake()` pour simuler les réponses Kumo basées sur les exemples du Swagger.
3. Vérifier que `Article::count()` et `Price::count()` correspondent aux données simulées.
4. Valider le mapping des catégories (`setArticleCategory`).

### 6.2 Validation de Performance
Comparer le temps de `importTcposAll()` avant et après migration. La réduction des appels (fusion prix/articles) devrait améliorer significativement les performances.
