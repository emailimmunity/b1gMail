<?php
/**
 * b1gMail Admin - Produkt-Verwaltung
 * Admin kann Produkte, Preise und Features verwalten
 */

require_once('../serverlib/admin.inc.php');

// Permissions-Check (Admin Session wird von admin.inc.php geprüft)
// Wenn wir hier sind, ist der User bereits authentifiziert

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';
$productID = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$message = '';
$messageType = '';

// ============================================
// AKTIONEN
// ============================================

// Produkt bearbeiten (Speichern)
if($action == 'save' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $productID = (int)$_POST['product_id'];
    
    $data = array(
        'product_name' => $_POST['product_name'],
        'product_description' => $_POST['product_description'],
        'base_price' => (float)$_POST['base_price'],
        'yearly_discount_percent' => (float)$_POST['yearly_discount_percent'],
        'grant_storage_gb' => (int)$_POST['grant_storage_gb'],
        'grant_domains' => (int)$_POST['grant_domains'],
        'grant_users' => (int)$_POST['grant_users'],
        'grant_subdomains' => (int)$_POST['grant_subdomains'],
        'grant_tenants' => (int)$_POST['grant_tenants'],
        'limit_email_in_mb' => (int)$_POST['limit_email_in_mb'],
        'limit_email_out_mb' => (int)$_POST['limit_email_out_mb'],
        'limit_aliases' => (int)$_POST['limit_aliases'],
        'limit_send_per_10min' => (int)$_POST['limit_send_per_10min'],
        'limit_pop3_accounts' => (int)$_POST['limit_pop3_accounts'],
        'enabled' => isset($_POST['enabled']) ? 1 : 0,
        'featured' => isset($_POST['featured']) ? 1 : 0
    );
    
    // Preis-Historie
    if($productID > 0) {
        $res = $db->Query('SELECT base_price FROM {pre}products WHERE product_id=?', $productID);
        if($res && $res->RowCount() > 0) {
            $old = $res->FetchArray(MYSQLI_ASSOC);
            if($old['base_price'] != $data['base_price']) {
                $db->Query('INSERT INTO {pre}product_price_history (product_id, old_price, new_price, changed_by, change_reason) VALUES (?,?,?,?,?)',
                    $productID,
                    $old['base_price'],
                    $data['base_price'],
                    $currentUser['id'],
                    'Admin-Änderung'
                );
            }
            $res->Free();
        }
    }
    
    // Update
    $db->Query('UPDATE {pre}products SET ' .
        'product_name=?, product_description=?, base_price=?, yearly_discount_percent=?, ' .
        'grant_storage_gb=?, grant_domains=?, grant_users=?, grant_subdomains=?, grant_tenants=?, ' .
        'limit_email_in_mb=?, limit_email_out_mb=?, limit_aliases=?, limit_send_per_10min=?, ' .
        'limit_pop3_accounts=?, enabled=?, featured=? WHERE product_id=?',
        $data['product_name'], $data['product_description'], $data['base_price'], $data['yearly_discount_percent'],
        $data['grant_storage_gb'], $data['grant_domains'], $data['grant_users'], $data['grant_subdomains'], $data['grant_tenants'],
        $data['limit_email_in_mb'], $data['limit_email_out_mb'], $data['limit_aliases'], $data['limit_send_per_10min'],
        $data['limit_pop3_accounts'], $data['enabled'], $data['featured'], $productID
    );
    
    $message = 'Produkt erfolgreich gespeichert!';
    $messageType = 'success';
    $action = 'list';
}

// Feature-Mappings speichern
if($action == 'save_features' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $productID = (int)$_POST['product_id'];
    $features = isset($_POST['features']) ? $_POST['features'] : array();
    
    // Alle deaktivieren
    $db->Query('UPDATE {pre}product_feature_map SET enabled=0 WHERE product_id=?', $productID);
    
    // Ausgewählte aktivieren
    foreach($features as $featureID) {
        $featureID = (int)$featureID;
        $exists = $db->Query('SELECT map_id FROM {pre}product_feature_map WHERE product_id=? AND feature_id=?', $productID, $featureID);
        if($exists->RowCount() > 0) {
            $db->Query('UPDATE {pre}product_feature_map SET enabled=1 WHERE product_id=? AND feature_id=?', $productID, $featureID);
        } else {
            $db->Query('INSERT INTO {pre}product_feature_map (product_id, feature_id, enabled) VALUES (?,?,1)', $productID, $featureID);
        }
        $exists->Free();
    }
    
    $message = 'Features gespeichert!';
    $messageType = 'success';
    $action = 'list';
}

// Produkt löschen
if($action == 'delete' && $productID > 0) {
    $db->Query('DELETE FROM {pre}products WHERE product_id=?', $productID);
    $message = 'Produkt gelöscht!';
    $messageType = 'success';
    $action = 'list';
}

// ============================================
// VIEWS
// ============================================

// Produktliste
if($action == 'list') {
    ?>
    <h1>Produkt-Verwaltung</h1>
    
    <?php if($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <p>
        <strong>24 Pakete</strong> für flexible Preisgestaltung | 
        <a href="?action=features">Features verwalten</a>
    </p>
    
    <style>
    .product-group {
        margin: 20px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
    }
    .product-group h2 {
        margin-top: 0;
        color: #007bff;
    }
    .product-item {
        padding: 10px;
        margin: 5px 0;
        background: #f8f9fa;
        border-left: 3px solid #007bff;
    }
    .product-item.disabled {
        opacity: 0.5;
        border-left-color: #ccc;
    }
    .product-item.featured {
        border-left-color: #ffc107;
        background: #fff3cd;
    }
    .product-price {
        font-size: 1.2em;
        font-weight: bold;
        color: #28a745;
    }
    .btn-group {
        float: right;
    }
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
        margin-left: 5px;
    }
    </style>
    
    <?php
    // Gruppen laden
    $groups = array(
        1 => 'USER (Email-Nutzer)',
        5 => 'DOMAIN (Domain-Besitzer)',
        10 => 'MULTI DOMAIN-ADMIN (Mehrere Domains)',
        50 => 'RESELLER (Mandanten-Anbieter)'
    );
    
    foreach($groups as $groupID => $groupName) {
        echo "<div class='product-group'>";
        echo "<h2>Gruppe {$groupID}: {$groupName}</h2>";
        
        $res = $db->Query('SELECT * FROM {pre}products WHERE target_group=? ORDER BY sort_order, base_price', $groupID);
        while($row = $res->FetchArray(MYSQLI_ASSOC)) {
            $class = 'product-item';
            if(!$row['enabled']) $class .= ' disabled';
            if($row['featured']) $class .= ' featured';
            
            echo "<div class='{$class}'>";
            echo "<div class='btn-group'>";
            echo "<a href='?action=edit&id={$row['product_id']}' class='btn btn-sm btn-primary'>Bearbeiten</a>";
            echo "<a href='?action=edit_features&id={$row['product_id']}' class='btn btn-sm btn-info'>Features</a>";
            echo "<a href='?action=delete&id={$row['product_id']}' onclick='return confirm(\"Wirklich löschen?\")' class='btn btn-sm btn-danger'>Löschen</a>";
            echo "</div>";
            
            echo "<strong>{$row['product_name']}</strong> ";
            echo "(<code>{$row['product_key']}</code>) ";
            if(!$row['enabled']) echo "<span style='color:red'>[DEAKTIVIERT]</span> ";
            if($row['featured']) echo "<span style='color:orange'>⭐ EMPFOHLEN</span> ";
            echo "<br>";
            
            echo "<span class='product-price'>{$row['base_price']} {$row['currency']}/Monat</span>";
            if($row['yearly_discount_percent'] > 0) {
                echo " (Jahres-Rabatt: {$row['yearly_discount_percent']}%)";
            }
            echo "<br>";
            
            echo "<small>";
            if($row['grant_storage_gb'] > 0) echo "💾 {$row['grant_storage_gb']} GB | ";
            if($row['grant_domains'] > 0) echo "🌐 {$row['grant_domains']} Domains | ";
            if($row['grant_users'] > 0) echo "👥 {$row['grant_users']} User | ";
            if($row['grant_tenants'] > 0) echo "🏢 {$row['grant_tenants']} Tenants | ";
            echo "📧 {$row['limit_email_in_mb']}MB/in {$row['limit_email_out_mb']}MB/out | ";
            echo "✉️ {$row['limit_send_per_10min']}/10min";
            echo "</small>";
            
            echo "</div>";
        }
        $res->Free();
        
        echo "</div>";
    }
    ?>
    
    <?php
}

// Produkt bearbeiten
elseif($action == 'edit' && $productID > 0) {
    $res = $db->Query('SELECT * FROM {pre}products WHERE product_id=?', $productID);
    if($res->RowCount() == 0) {
        die('Produkt nicht gefunden!');
    }
    $product = $res->FetchArray(MYSQLI_ASSOC);
    $res->Free();
    ?>
    
    <h1>Produkt bearbeiten: <?php echo htmlspecialchars($product['product_name']); ?></h1>
    
    <form method="POST" action="?action=save">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        
        <h3>Basis-Informationen</h3>
        <table class="border">
            <tr>
                <td>Name:</td>
                <td><input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" size="40" required></td>
            </tr>
            <tr>
                <td>Key:</td>
                <td><code><?php echo htmlspecialchars($product['product_key']); ?></code> (nicht änderbar)</td>
            </tr>
            <tr>
                <td>Gruppe:</td>
                <td><?php echo $product['target_group']; ?> (<?php echo $product['product_category']; ?>)</td>
            </tr>
            <tr>
                <td>Beschreibung:</td>
                <td><textarea name="product_description" rows="3" cols="40"><?php echo htmlspecialchars($product['product_description']); ?></textarea></td>
            </tr>
            <tr>
                <td>Status:</td>
                <td>
                    <input type="checkbox" name="enabled" value="1" <?php echo $product['enabled'] ? 'checked' : ''; ?>> Aktiv &nbsp;
                    <input type="checkbox" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?>> Empfohlen (⭐ Badge)
                </td>
            </tr>
        </table>
        
        <h3>Preis & Billing</h3>
        <table class="border">
            <tr>
                <td>Basis-Preis:</td>
                <td><input type="number" name="base_price" value="<?php echo $product['base_price']; ?>" step="0.01" min="0" required> EUR/Monat</td>
            </tr>
            <tr>
                <td>Jahres-Rabatt:</td>
                <td><input type="number" name="yearly_discount_percent" value="<?php echo $product['yearly_discount_percent']; ?>" step="0.01" min="0" max="100"> %</td>
            </tr>
        </table>
        
        <h3>Ressourcen</h3>
        <table class="border">
            <tr>
                <td>Speicher:</td>
                <td><input type="number" name="grant_storage_gb" value="<?php echo $product['grant_storage_gb']; ?>" min="0"> GB</td>
            </tr>
            <tr>
                <td>Domains:</td>
                <td><input type="number" name="grant_domains" value="<?php echo $product['grant_domains']; ?>" min="0"> (nur Gruppe 5+)</td>
            </tr>
            <tr>
                <td>User-Accounts:</td>
                <td><input type="number" name="grant_users" value="<?php echo $product['grant_users']; ?>" min="0"> (nur Gruppe 5+)</td>
            </tr>
            <tr>
                <td>Subdomains:</td>
                <td><input type="number" name="grant_subdomains" value="<?php echo $product['grant_subdomains']; ?>" min="0"></td>
            </tr>
            <tr>
                <td>Tenants:</td>
                <td><input type="number" name="grant_tenants" value="<?php echo $product['grant_tenants']; ?>" min="0"> (nur Gruppe 50)</td>
            </tr>
        </table>
        
        <h3>Limits</h3>
        <table class="border">
            <tr>
                <td>E-Mail eingehend:</td>
                <td><input type="number" name="limit_email_in_mb" value="<?php echo $product['limit_email_in_mb']; ?>" min="0"> MB</td>
            </tr>
            <tr>
                <td>E-Mail ausgehend:</td>
                <td><input type="number" name="limit_email_out_mb" value="<?php echo $product['limit_email_out_mb']; ?>" min="0"> MB</td>
            </tr>
            <tr>
                <td>Aliase:</td>
                <td><input type="number" name="limit_aliases" value="<?php echo $product['limit_aliases']; ?>" min="0"></td>
            </tr>
            <tr>
                <td>Versand-Limit:</td>
                <td><input type="number" name="limit_send_per_10min" value="<?php echo $product['limit_send_per_10min']; ?>" min="0"> E-Mails / 10 Min</td>
            </tr>
            <tr>
                <td>POP3-Accounts:</td>
                <td><input type="number" name="limit_pop3_accounts" value="<?php echo $product['limit_pop3_accounts']; ?>" min="0"></td>
            </tr>
        </table>
        
        <p>
            <button type="submit" class="btn">💾 Speichern</button>
            <a href="?action=list" class="btn">Abbrechen</a>
            <a href="?action=edit_features&id=<?php echo $product['product_id']; ?>" class="btn">Features bearbeiten →</a>
        </p>
    </form>
    
    <?php
}

// Features bearbeiten
elseif($action == 'edit_features' && $productID > 0) {
    $res = $db->Query('SELECT * FROM {pre}products WHERE product_id=?', $productID);
    if($res->RowCount() == 0) {
        die('Produkt nicht gefunden!');
    }
    $product = $res->FetchArray(MYSQLI_ASSOC);
    $res->Free();
    
    // Aktuell aktive Features
    $activeFeatures = array();
    $res = $db->Query('SELECT feature_id FROM {pre}product_feature_map WHERE product_id=? AND enabled=1', $productID);
    while($row = $res->FetchArray(MYSQLI_ASSOC)) {
        $activeFeatures[] = $row['feature_id'];
    }
    $res->Free();
    ?>
    
    <h1>Features für: <?php echo htmlspecialchars($product['product_name']); ?></h1>
    
    <form method="POST" action="?action=save_features">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        
        <?php
        $categories = array(
            'basis' => '⚙️ BASIS-FEATURES (POP3, IMAP, etc.)',
            'standard' => '📦 STANDARD-FEATURES (WebDAV, Autoresponder, etc.)',
            'premium' => '⭐ PREMIUM-FEATURES (S/MIME, Support, etc.)',
            'domain' => '🌐 DOMAIN-FEATURES (DNS, Subdomains, etc.)',
            'admin' => '👑 ADMIN-FEATURES (API, User-Mgmt, etc.)',
            'reseller' => '🏢 RESELLER-FEATURES (Tenants, White-Label, etc.)'
        );
        
        foreach($categories as $cat => $catName) {
            echo "<h3>{$catName}</h3>";
            echo "<div style='margin-left:20px; margin-bottom:20px;'>";
            
            $res = $db->Query('SELECT * FROM {pre}product_features WHERE feature_category=? ORDER BY sort_order', $cat);
            while($row = $res->FetchArray(MYSQLI_ASSOC)) {
                $checked = in_array($row['feature_id'], $activeFeatures) ? 'checked' : '';
                echo "<label style='display:block; margin:5px 0;'>";
                echo "<input type='checkbox' name='features[]' value='{$row['feature_id']}' {$checked}> ";
                echo "<strong>{$row['feature_name']}</strong> ";
                echo "<small style='color:#666;'>({$row['feature_key']}) - {$row['feature_description']}</small>";
                echo "</label>";
            }
            $res->Free();
            
            echo "</div>";
        }
        ?>
        
        <p>
            <button type="submit" class="btn">💾 Features speichern</button>
            <a href="?action=edit&id=<?php echo $product['product_id']; ?>" class="btn">← Zurück zu Produkt</a>
            <a href="?action=list" class="btn">Zur Liste</a>
        </p>
    </form>
    
    <?php
}

// Feature-Verwaltung
elseif($action == 'features') {
    ?>
    <h1>Feature-Verwaltung</h1>
    
    <p><a href="?action=list">← Zurück zur Produktliste</a></p>
    
    <?php
    $categories = array(
        'basis' => '⚙️ BASIS',
        'standard' => '📦 STANDARD',
        'premium' => '⭐ PREMIUM',
        'domain' => '🌐 DOMAIN',
        'admin' => '👑 ADMIN',
        'reseller' => '🏢 RESELLER'
    );
    
    foreach($categories as $cat => $catName) {
        echo "<h3>{$catName}-FEATURES</h3>";
        echo "<table class='border'>";
        echo "<tr><th>Name</th><th>Key</th><th>Beschreibung</th><th>Min. Gruppe</th><th>Aktiv</th></tr>";
        
        $res = $db->Query('SELECT * FROM {pre}product_features WHERE feature_category=? ORDER BY sort_order', $cat);
        while($row = $res->FetchArray(MYSQLI_ASSOC)) {
            echo "<tr>";
            echo "<td><strong>{$row['feature_name']}</strong></td>";
            echo "<td><code>{$row['feature_key']}</code></td>";
            echo "<td>{$row['feature_description']}</td>";
            echo "<td>Gruppe {$row['min_group']}+</td>";
            echo "<td>" . ($row['enabled'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        $res->Free();
        
        echo "</table>";
    }
    ?>
    
    <?php
}
?>
