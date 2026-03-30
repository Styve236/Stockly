# TODO Corrections Bugs Stockly (Post-approbation)

**Bugs à fixer (plan détaillé):**

1. **api/users.php**:
   - Ajouter `if (!isLoggedIn()) { 401 error }`
   - Ajouter case 'update': UPDATE users SET username, role, email, password (hash if provided)

2. **admin/users.php**:
   - Bouton "Éditer" par user → modal prefill (username, role, email, pass optionnel)
   - Form action='update', hidden id

3. **dashboard.php**:
   - `if ($role !== 'guichet' && !empty($low_stock))` pour alert div
   - JS `if ('<?php echo $role; ?>' !== 'guichet') checkStockAlerts()`

4. **index.php**:
   - `<h1 class="text-success mb-1">Stockly</h1>` (vert)

5. **assets/css/stockly.css**:
   - `.login-container h1 { color: var(--primary-green) !important; }`

6. **orders.php (guichet multi-items)**:
   - Modal: Liste dynamique lignes (select prod + qty + × suppr)
   - JS: addRow(), removeRow(), submit avec items JSON/array
   - Bouton "Envoyer commande"

7. **api/orders.php**:
   - case 'create': Parse $_POST['items'] → create order → loop insert multiple order_items

**Ordre exécution:**
1. Fixes users (api + admin)
2. Dashboard + login CSS
3. Orders multi-items (api + page + JS)

**Après fixes:** Test full + attempt_completion.

**Users ✅** (login check, update API, edit modal JS)
**Dashboard/login CSS ✅** (alertes guichet cachées, titre vert)
**api/orders.php ✅** (multi-items JSON)

**api/orders ✅** multi-items
**orders.php modal + JS ✅**
**Visibility: Voir Détails ✅** (API + Page + Modal)
**Inventory: Supplier select ✅**

**7/7 ✅ - PROJET TERMINÉ**


