# miniframwork_php
# Mini Framework PHP - AppDAF

Un mini framework PHP moderne avec injection de dépendances, conteneur IoC, et architecture MVC pour le développement d'APIs REST.

## 🚀 Installation

### Prérequis
- PHP 8.0 ou supérieur
- Composer
- PostgreSQL (par défaut) ou MySQL
- Extension PHP PDO

### Installation via Composer

```bash
composer create-project moustapha/appdaf mon-projet
cd mon-projet
```

### Installation manuelle

```bash
git clone https://github.com/moustapha/appdaf.git
cd appdaf
composer install
```

## ⚙️ Configuration

### 1. Variables d'environnement

Créez un fichier `.env` à la racine du projet :

```env
# Base de données
DB_DRIVER=pgsql
DB_HOST=localhost
DB_PORT=5433
DB_NAME=pgdbDaf
DB_USER=pguserDaf
DB_PASSWORD=pgpassword

# API
API_BASE_URL=http://localhost:8081
```

### 2. Configuration des services

Le fichier `app/config/services.yml` configure l'injection de dépendances :

```yaml
services:
  repositories:
    CitoyenRepository:
      class: App\Repository\CitoyenRepository
      singleton: true
  
  services:
    CitoyenService:
      class: App\Service\CitoyenService
      dependencies:
        - CitoyenRepository
        - LoggerService
      singleton: true
  
  controllers:
    CitoyenController:
      class: App\Controller\CitoyenController
      dependencies:
        - CitoyenService
      singleton: true
```

## 🗄️ Base de données

### Migration

```bash
# Exécuter les migrations
composer run database:migrate

# Reset et exécuter les migrations
composer run database:migrate -- --reset
```

### Seeders

```bash
# Insérer les données de test
composer run seeder:migrate

# Reset et insérer les données
composer run seeder:migrate -- --reset
```

## 🎯 Architecture

### Structure du projet

```
app/
├── config/          # Configuration
│   ├── bootstrap.php
│   ├── env.php
│   ├── helpers.php
│   └── services.yml
├── core/           # Cœur du framework
│   ├── abstract/   # Classes abstraites
│   ├── App.php     # Application principale
│   ├── Container.php # Injection de dépendances
│   ├── Router.php  # Système de routage
│   └── Session.php # Gestion des sessions
src/
├── controller/     # Contrôleurs
├── entity/        # Entités métier
├── repository/    # Couche d'accès aux données
├── service/       # Services métier
└── enum/          # Énumérations
routes/
└── route.web.php  # Définition des routes
migrations/        # Migrations de base de données
seeders/          # Données de test
public/
└── index.php     # Point d'entrée
```

### Modèle MVC

#### 1. Entités

```php
<?php
namespace App\Entity;

use App\Core\Abstract\AbstractEntity;

class Citoyen extends AbstractEntity
{
    private string $nci;
    private string $nom;
    private string $prenom;

    public static function toObject(array $data): static
    {
        $citoyen = new self();
        $citoyen->nci = $data['nci'];
        $citoyen->nom = $data['nom'];
        $citoyen->prenom = $data['prenom'];
        return $citoyen;
    }

    public function toArray(): array
    {
        return [
            'nci' => $this->nci,
            'nom' => $this->nom,
            'prenom' => $this->prenom
        ];
    }
}
```

#### 2. Repositories

```php
<?php
namespace App\Repository;

use App\Core\Abstract\AbstractRepository;
use App\Entity\Citoyen;

class CitoyenRepository extends AbstractRepository
{
    public function selectAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM citoyens");
        return $stmt->fetchAll();
    }

    public function selectById(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM citoyens WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function selectBy(array $filtre): array
    {
        // Implémentation des filtres
    }

    public function insert(): void
    {
        // Implémentation insertion
    }

    public function update(): void
    {
        // Implémentation mise à jour
    }

    public function delete(): void
    {
        // Implémentation suppression
    }
}
```

#### 3. Services

```php
<?php
namespace App\Service;

class CitoyenService
{
    public function __construct(
        private CitoyenRepository $repository,
        private LoggerService $logger
    ) {}

    public function getAllCitoyens(): array
    {
        $this->logger->log("Récupération de tous les citoyens");
        return $this->repository->selectAll();
    }

    public function getCitoyenByNci(string $nci): ?Citoyen
    {
        $data = $this->repository->selectBy(['nci' => $nci]);
        return $data ? Citoyen::toObject($data[0]) : null;
    }
}
```

#### 4. Contrôleurs

```php
<?php
namespace App\Controller;

use App\Core\Abstract\AbstractController;

class CitoyenController extends AbstractController
{
    public function __construct(
        private CitoyenService $service
    ) {
        parent::__construct();
    }

    public function index(): void
    {
        $citoyens = $this->service->getAllCitoyens();
        $this->renderJson($citoyens, "success", 200, "Liste des citoyens");
    }

    public function show(): void
    {
        $nci = $_GET['nci'] ?? null;
        if (!$nci) {
            $this->renderJson(null, "error", 400, "NCI requis");
            return;
        }

        $citoyen = $this->service->getCitoyenByNci($nci);
        if (!$citoyen) {
            $this->renderJson(null, "error", 404, "Citoyen non trouvé");
            return;
        }

        $this->renderJson($citoyen->toArray(), "success", 200);
    }
}
```

## 🛣️ Routage

### Définition des routes

Dans `routes/route.web.php` :

```php
<?php
return [
    [
        'method' => 'GET',
        'path' => '/citoyens',
        'controller' => 'CitoyenController',
        'action' => 'index'
    ],
    [
        'method' => 'GET',
        'path' => '/citoyen/nci/{nci}',
        'controller' => 'CitoyenController',
        'action' => 'findByNci'
    ],
    [
        'method' => 'POST',
        'path' => '/citoyens',
        'controller' => 'CitoyenController',
        'action' => 'store'
    ],
    [
        'method' => 'GET',
        'path' => '/health',
        'action' => function() {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok']);
        }
    ]
];
```

### Paramètres d'URL

- `{parametre}` : Paramètre obligatoire
- Accès via les paramètres de méthode du contrôleur

## 🔧 Conteneur IoC

### Utilisation

```php
// Récupérer un service
$service = App::get('CitoyenService');

// Accès direct au conteneur
$container = App::getContainer();
$service = $container->resolve(CitoyenService::class);
```

### Enregistrement manuel

```php
$container = App::getContainer();

// Binding simple
$container->bind(Interface::class, Implementation::class);

// Singleton
$container->singleton(Service::class);

// Instance existante
$container->instance('myService', $serviceInstance);
```

## 📊 Base de données

### Connexion

La connexion est automatiquement gérée via la classe `Database` :

```php
use App\Core\Abstract\Database;

$pdo = Database::getConnection();
```

### Migrations

Créez des fichiers SQL dans `migrations/` :

```sql
-- 001_create_users_table.sql
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🔄 Sessions

```php
use App\Core\Session;

$session = Session::getInstance();

// Définir une valeur
$session->set('user_id', 123);

// Récupérer une valeur
$userId = Session::get('user_id');

// Vérifier l'existence
if ($session->has('user_id')) {
    // ...
}

// Supprimer
$session->unset('user_id');

// Détruire la session
$session->destroy();
```

## 🚀 Démarrage

### Serveur de développement

```bash
composer run start
# ou
php -S localhost:8081 -t public
```

### Test de l'API

```bash
# Health check
curl http://localhost:8081/health

# Liste des citoyens
curl http://localhost:8081/citoyens

# Recherche par NCI
curl http://localhost:8081/citoyen/nci/NCI123456
```

## 🧪 Tests

### Structure des réponses JSON

Toutes les réponses suivent le format :

```json
{
    "data": {...},
    "statut": "success|error",
    "code": 200,
    "message": "Message descriptif"
}
```

### Codes de statut

- `200` : Succès
- `201` : Créé
- `400` : Erreur de validation
- `404` : Non trouvé
- `500` : Erreur serveur

## 🛡️ Sécurité

### Middleware d'authentification

```php
// Dans les routes
[
    'method' => 'GET',
    'path' => '/admin/users',
    'controller' => 'UserController',
    'action' => 'index',
    'middlewares' => ['auth']
]
```

### Helper de debug

```php
// Utiliser dd() pour debug
dd($variable); // Affiche et arrête l'exécution
```

## 📝 Bonnes pratiques

1. **Injection de dépendances** : Utilisez le conteneur IoC
2. **Single Responsibility** : Une classe = une responsabilité
3. **Typage strict** : Utilisez les types PHP
4. **Gestion d'erreurs** : Utilisez les exceptions
5. **Tests** : Écrivez des tests unitaires

## 🤝 Contribution

1. Fork le projet
2. Créez une branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Committez (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Push (`git push origin feature/nouvelle-fonctionnalite`)
5. Créez une Pull Request

## 📄 Licence

Ce projet est sous licence propriétaire.

## 👨‍💻 Auteur

**Moustapha Ndiaye**

---

*Pour plus d'informations, consultez la documentation ou créez une issue sur GitHub.*