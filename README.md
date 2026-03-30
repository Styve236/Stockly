# Stockly - Gestion des Bons de Commande & Inventaire pour FIGEC

## Installation (WAMP + VSCode)

1. **Démarrer WAMP** (Apache + MySQL/phpMyAdmin)
2. **Créer DB**: Ouvrir phpMyAdmin → Nouveau → Nom: `stockly` → Importer `stockly.sql`
3. **Libs requises** (télécharger dans `Stockly/libs/`):
   - [TCPDF](https://tcpdf.org) pour PDF
   - [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) pour Excel
4. **Ouvrir projet**: VSCode → File → Open Folder → `C:/Users/Johan/Desktop/Stockly`
5. **Lancer**: Browser → `http://localhost/Stockly/`
6. **Logins par défaut** (mot de passe: `password` pour tous):
   | User | Role |
   |------|------|
   | admin | Admin |
   | stock_mgr | Gestionnaire Stock |
   | guichet | Guichet |

## Structure
```
Stockly/
├── stockly.sql      # DB
├── config.php       # Connexion DB
├── index.php        # Login (à créer)
├── dashboard.php    # Tableau de bord
├── api/             # Endpoints PHP
├── assets/          # CSS/JS
├── libs/            # TCPDF, PhpSpreadsheet
└── TODO.md          # Progression
```

## Prochaines étapes
Suivez TODO.md. L'app fonctionnera localement une fois complète.

**Thème**: Blanc & Vert FIGEC ✅

