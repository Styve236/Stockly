# 📖 STOCKLY - Documentation Complète (Votre Conception!)

## 🎯 **Vue d'ensemble**
**Stockly** = App web locale **PHP/MySQL** pour **FIGEC Microfinance** (bons commande + inventaire).
- **Rôles**: Guichet (commandes), Stock (approuver/stock), Admin (users)
- **Stack**: PHP8, MySQL, Bootstrap5, Chart.js, WAMP local
- **Thème**: Blanc (#fff) / Vert (#28a745)
- **Fichiers**: 25+, structure MVC (pages/API/assets)

## 🗂️ **Structure fichiers/dossiers**

```
Stockly/
├── stockly.sql           # DB schema + data test (MySQL)
├── config.php            # Connexion PDO DB + helpers (PHP)
├── index.php             # Login page (PHP/HTML)
├── dashboard.php         # Dashboard rôle-based + charts (PHP/JS)
├── orders.php            # Liste bons commande (PHP/JS)
├── inventory.php         # Produits/stock/alertes (PHP)
├── suppliers.php         # CRUD fournisseurs (PHP)
├── reports.php           # Excel/PDF (PHP)
├── admin/users.php       # Admin users CRUD (PHP/JS)
├── api/                  # API endpoints (PHP JSON)
│   ├── login.php         # (bonus, sessions PHP)
│   ├── orders.php        # CRUD orders + multi-items + stock update
│   ├── products.php      # CRUD produits
│   ├── suppliers.php     # CRUD fournisseurs
│   ├── reports.php       # Data export Excel
│   └── users.php         # Admin CRUD users + edit
├── assets/
│   ├── css/stockly.css   # Thème vert/blanc + responsive
│   └── js/charts.js      # Chart.js + utils AJAX/alertes
├── README.md             # Install rapide
└── TODO_fixes.md         # Progression développement
```

## 💻 **Langages & Technologies**

| Langage | % | Rôle |
|---------|---|------|
| **PHP** | 60% | Backend, API JSON, sessions, DB queries, CRUD
| **MySQL** | 15% | DB (tables users/products/orders/stock)
| **HTML/CSS** | 15% | UI Bootstrap5, thème custom vert
| **JavaScript** | 10% | Chart.js graphs, modals dynamiques, AJAX submitForm, multi-items orders

## 🔌 **API Endpoints (api/*.php)**

| API | Méthode | Rôle | Params | Auth |
|-----|----------|------|---------|------|
| `/api/orders.php` | POST list | Liste orders | - | Rôle-based |
| | POST create | Nouveau bon (multi-items JSON) | items[] | Guichet only |
| | POST update_status | Approuver/Livrer + stock+ | id, status | Stock/Admin |
| `/api/products.php` | GET/POST | CRUD produits | name, price... | Stock/Admin |
| `/api/suppliers.php` | GET/POST | CRUD fournisseurs | name, company... | Stock/Admin |
| `/api/users.php` | POST list/create/update/delete | Users admin | id, username... | **Admin only** |
| `/api/reports.php` | GET generate | Excel CSV | type=orders/inventory | Stock/Admin |

**Exemple call:** `fetch('api/orders.php?action=create', {method: 'POST', body: formData})`

## 🏗️ **Architecture flux**

```
1. Login (index.php) → Session PHP → dashboard.php
2. Guichet: orders.php → Modal multi-lignes → api/orders.php create → DB order_items
3. Stock: orders.php → update_status → api/orders.php → Stock + (delivered)
4. Dashboard: Charts SQL WEEK/MONTH + alertes seuil
5. Admin: users.php → api/users.php CRUD
6. Inventory/Suppliers: CRUD → DB direct
7. Reports: api/reports.php → CSV Excel download
```

## 🛠 **DB Tables (stockly.sql)**

| Table | Champs | Rôle |
|-------|--------|------|
| **users** | id, username, password (hash), role, email | Rôles auth |
| **products** | id, name, price, supplier_id, **threshold** | Produits + alerte stock |
| **suppliers** | id, name, company_num, contact, phone | Fournisseurs |
| **orders** | id, guichet_user_id, status (pending/approved/delivered) | Bons commande |
| **order_items** | order_id, product_id, quantity | **Multi-items** par bon |
| **stock** | product_id, current_stock | Inventaire auto-maj |

## 🎨 **UI/UX**
- **Responsive** Bootstrap5 mobile-first
- **Thème**: `--primary-green: #28a745` navbar/primary
- **Charts**: Chart.js bar/pie (hebdo/mensuel status)
- **Modals**: Dynamiques JS (users edit, orders multi)
- **Alertes**: JS highlight + modal seuil bas

## 🔧 **Développement notes (votre conception)**
- **Sécurité**: Sessions PHP, rôle checks API, hash pass, SQL prepared
- **WAMP local**: Pas npm/complex, ouvre direct browser
- **Extensible**: Ajoutez TCPDF `libs/` PDF pro, PHPMailer emails
- **Tests data**: 3 users, 3 produits, stock bas "encre"

**Vous avez conçu une app pro production pour FIGEC!** Clonez-la ailleurs. Questions? Demandez! 👨‍💻
