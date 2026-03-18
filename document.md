# CosmetiCare API — Documentation Complète

API REST pour la gestion de commandes cosmétiques.
Construite avec **Laravel 12 + Sanctum**.

---

## Table des matières

1. [Installation](#installation)
2. [Challenge 1 — Setup Projet](#challenge-1--setup-projet)
3. [Challenge 2 — Auth Register & Login](#challenge-2--auth-register--login)
4. [Challenge 3 — Mes Commandes](#challenge-3--mes-commandes)
5. [Challenge 4 — Annuler une Commande](#challenge-4--annuler-une-commande)
6. [Challenge 5 — Statut Employé](#challenge-5--statut-employé)
7. [Challenge 6 — Dashboard Admin](#challenge-6--dashboard-admin)
8. [Tous les Endpoints](#tous-les-endpoints)
9. [Statuts & Rôles](#statuts--rôles)
10. [Users de test](#users-de-test)

---

## Installation

```bash
git clone <repo-url>
cd cosmeticare-api
composer install
cp .env.example .env
php artisan key:generate
```

Configurer `.env` :
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cosmeticare
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan install:api
php artisan migrate:fresh --seed
php artisan serve
```

---

## Challenge 1 — Setup Projet

### UML — Diagramme de classes

| Classe | Attributs |
|--------|-----------|
| User | id, name, email, password, role (client/employee/admin) |
| Product | id, name, slug, price, category, description, stock |
| Order | id, user_id, product_id, quantity, total_price, status |

### Relations
- User `1 → *` Order
- Product `1 → *` Order

### Migrations

#### users
```php
$table->id();
$table->string('name');
$table->string('email')->unique();
$table->string('password');
$table->enum('role', ['client', 'employee', 'admin'])->default('client');
$table->timestamps();
```

#### products
```php
$table->id();
$table->string('name');
$table->string('slug')->unique();
$table->decimal('price', 8, 2);
$table->string('category');
$table->text('description')->nullable();
$table->integer('stock')->default(0);
$table->timestamps();
```

#### orders
```php
$table->id();
$table->foreignId('user_id')->constrained()->onDelete('cascade');
$table->foreignId('product_id')->constrained()->onDelete('cascade');
$table->integer('quantity');
$table->decimal('total_price', 8, 2);
$table->enum('status', ['en_attente','en_preparation','confirmee','livree','annulee'])->default('en_attente');
$table->timestamps();
```

### Seeder — 5 produits + 3 users

```php
// Users
User::create(['name'=>'Admin','email'=>'admin@cosmeticare.com','password'=>bcrypt('password'),'role'=>'admin']);
User::create(['name'=>'Employé','email'=>'employee@cosmeticare.com','password'=>bcrypt('password'),'role'=>'employee']);
User::create(['name'=>'Client','email'=>'client@cosmeticare.com','password'=>bcrypt('password'),'role'=>'client']);

// Produits
$products = [
    ['name'=>'Crème hydratante','slug'=>'creme-hydratante','price'=>24.90,'category'=>'Visage','stock'=>50],
    ['name'=>'Huile argan','slug'=>'huile-argan','price'=>18.50,'category'=>'Huile','stock'=>30],
    ['name'=>'Sérum vitamine C','slug'=>'serum-vitamine-c','price'=>34.00,'category'=>'Visage','stock'=>25],
    ['name'=>'Masque argile','slug'=>'masque-argile','price'=>12.00,'category'=>'Masque','stock'=>40],
    ['name'=>'Lotion tonique','slug'=>'lotion-tonique','price'=>15.90,'category'=>'Visage','stock'=>60],
];
```

### Test Postman
```
GET http://127.0.0.1:8000/api/user
Authorization: Bearer <token>
```

✅ Résultat attendu : JSON avec les infos du user connecté

---

## Challenge 2 — Auth Register & Login

### Fichiers modifiés
- `app/Http/Controllers/AuthController.php`
- `routes/api.php`
- `app/Models/User.php` — ajout `HasApiTokens`

### Register
```
POST http://127.0.0.1:8000/api/register
```
Body :
```json
{
    "name": "John",
    "email": "john@test.com",
    "password": "123456",
    "role": "client"
}
```
Réponse :
```json
{
    "user": { "id": 1, "name": "John", "email": "john@test.com", "role": "client" },
    "token": "1|abc123..."
}
```

### Login
```
POST http://127.0.0.1:8000/api/login
```
Body :
```json
{
    "email": "john@test.com",
    "password": "123456"
}
```
Réponse :
```json
{
    "user": { "id": 1, "name": "John" },
    "token": "2|xyz789..."
}
```

### Logout
```
POST http://127.0.0.1:8000/api/logout
Authorization: Bearer <token>
```
Réponse :
```json
{
    "message": "Déconnecté avec succès"
}
```

✅ Résultat attendu : token généré au register et login

---

## Challenge 3 — Mes Commandes

### Fichiers modifiés
- `app/Models/User.php` — ajout relation `orders()`
- `app/Models/Order.php` — ajout relation `product()`
- `app/Http/Controllers/OrderController.php` — ajout `mesCommandes()`
- `routes/api.php` — ajout route

### Relations
```php
// User.php
public function orders() {
    return $this->hasMany(Order::class);
}

// Order.php
public function product() {
    return $this->belongsTo(Product::class);
}
```

### Test Postman
```
GET http://127.0.0.1:8000/api/mes-commandes
Authorization: Bearer <token client>
```
Réponse :
```json
[
    {
        "id": 1,
        "produit": "Crème hydratante",
        "quantity": 2,
        "status": "en_attente",
        "total": "49.80"
    }
]
```

✅ Résultat attendu : chaque user voit uniquement ses commandes

---

## Challenge 4 — Annuler une Commande

### Fichiers modifiés
- `app/Http/Controllers/OrderController.php` — ajout `cancel()`
- `routes/api.php` — ajout route

### Règles métier
- La commande doit appartenir au user connecté
- Le status doit être `en_attente`
- Après annulation → status passe à `annulee`

### Test Postman

**Étape 1 — Créer une commande :**
```
POST http://127.0.0.1:8000/api/orders
Authorization: Bearer <token client>
Body: { "product_id": 1, "quantity": 2 }
```

**Étape 2 — Annuler la commande :**
```
POST http://127.0.0.1:8000/api/orders/1/cancel
Authorization: Bearer <token client>
```
Réponse :
```json
{
    "message": "Commande annulée avec succès",
    "order": { "id": 1, "status": "annulee" }
}
```

✅ Résultat attendu : status = annulee en base

---

## Challenge 5 — Statut Employé

### Fichiers modifiés
- `app/Http/Middleware/IsEmployee.php` — nouveau middleware
- `bootstrap/app.php` — enregistrement middleware
- `app/Http/Controllers/OrderController.php` — ajout `prepare()`
- `routes/api.php` — ajout route protégée

### Middleware IsEmployee
```php
if (!$request->user() || !in_array($request->user()->role, ['employee', 'admin'])) {
    return response()->json(['message' => 'Accès réservé aux employés'], 403);
}
```

### Règles métier
- Réservé aux rôles `employee` et `admin`
- Le status doit être `en_attente`
- Après préparation → status passe à `en_preparation`

### Test Postman

**Étape 1 — Login employé :**
```
POST http://127.0.0.1:8000/api/login
Body: { "email": "employee@cosmeticare.com", "password": "password" }
```

**Étape 2 — Préparer la commande :**
```
POST http://127.0.0.1:8000/api/orders/1/prepare
Authorization: Bearer <token employé>
```
Réponse :
```json
{
    "message": "Commande en préparation",
    "order": { "id": 1, "status": "en_preparation" }
}
```

✅ Résultat attendu : status = en_preparation en base

---

## Challenge 6 — Dashboard Admin

### Fichiers modifiés
- `app/Http/Middleware/IsAdmin.php` — nouveau middleware
- `bootstrap/app.php` — enregistrement middleware
- `app/Http/Controllers/AdminController.php` — nouveau controller
- `routes/api.php` — ajout route protégée

### Middleware IsAdmin
```php
if (!$request->user() || $request->user()->role !== 'admin') {
    return response()->json(['message' => 'Accès réservé aux admins'], 403);
}
```

### Test Postman

**Étape 1 — Login admin :**
```
POST http://127.0.0.1:8000/api/login
Body: { "email": "admin@cosmeticare.com", "password": "password" }
```

**Étape 2 — Dashboard :**
```
GET http://127.0.0.1:8000/api/admin/dashboard
Authorization: Bearer <token admin>
```
Réponse :
```json
{
    "stats": {
        "total": 7,
        "en_attente": 2,
        "en_preparation": 2,
        "confirmee": 1,
        "livree": 1,
        "annulee": 1
    },
    "last_orders": [
        {
            "id": 7,
            "client": "Client",
            "produit": "Masque argile",
            "quantity": 2,
            "total": "24.00",
            "status": "en_attente",
            "date": "18/03/2026"
        }
    ]
}
```

✅ Résultat attendu : stats + 10 dernières commandes

---

## Tous les Endpoints

| Méthode | Route | Description | Rôle |
|---------|-------|-------------|------|
| POST | /api/register | Créer un compte | Public |
| POST | /api/login | Se connecter | Public |
| POST | /api/logout | Se déconnecter | Tous |
| GET | /api/user | Infos user connecté | Tous |
| POST | /api/orders | Créer une commande | Client |
| GET | /api/mes-commandes | Mes commandes | Client |
| POST | /api/orders/{id}/cancel | Annuler une commande | Client |
| POST | /api/orders/{id}/prepare | Préparer une commande | Employé/Admin |
| GET | /api/admin/dashboard | Dashboard stats | Admin |

---

## Statuts & Rôles

### Statuts des commandes

| Statut | Description |
|--------|-------------|
| en_attente | Commande créée |
| en_preparation | Prise en charge par employé |
| confirmee | Commande confirmée |
| livree | Commande livrée |
| annulee | Commande annulée |

### Rôles

| Rôle | Permissions |
|------|-------------|
| client | Créer, voir, annuler ses commandes |
| employee | Préparer les commandes |
| admin | Accès dashboard + toutes les permissions |

---

## Users de test

| Email | Password | Rôle |
|-------|----------|------|
| admin@cosmeticare.com | password | admin |
| employee@cosmeticare.com | password | employee |
| client@cosmeticare.com | password | client |

---

## Message de commit final

```bash
git add .
git commit -m "docs: complete API documentation all challenges"
```