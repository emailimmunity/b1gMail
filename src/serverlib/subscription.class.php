<?php
/**
 * b1gMail Subscription Management
 * Verwaltet User-Abonnements nach Produkt-Kauf
 */

class BMSubscription {
    
    /**
     * Subscription nach erfolgreicher Zahlung aktivieren
     */
    public static function ActivateFromOrder($orderID) {
        global $db;
        
        // Order laden
        $res = $db->Query('SELECT * FROM {pre}orders WHERE id=?', $orderID);
        if($res->RowCount() == 0) {
            return false;
        }
        $order = $res->FetchArray(MYSQLI_ASSOC);
        $res->Free();
        
        // Order-Items laden
        $res = $db->Query('SELECT * FROM {pre}orderitems WHERE orderid=?', $orderID);
        while($item = $res->FetchArray(MYSQLI_ASSOC)) {
            if($item['type'] == 'product_subscription') {
                $data = unserialize($item['data']);
                
                // Subscription erstellen
                self::CreateSubscription(
                    $order['userid'],
                    $data['product_id'],
                    $data['billing_period'],
                    $data['grants'],
                    $data['limits'],
                    $orderID
                );
            }
        }
        $res->Free();
        
        return true;
    }
    
    /**
     * Neue Subscription erstellen
     */
    public static function CreateSubscription($userID, $productID, $billingPeriod, $grants, $limits, $orderID = null) {
        global $db;
        
        // Produkt laden
        $res = $db->Query('SELECT * FROM {pre}products WHERE product_id=?', $productID);
        if($res->RowCount() == 0) {
            return false;
        }
        $product = $res->FetchArray(MYSQLI_ASSOC);
        $res->Free();
        
        // Laufzeit berechnen
        $startDate = time();
        if($billingPeriod == 'yearly') {
            $endDate = strtotime('+1 year', $startDate);
            $renewalDate = $endDate;
        } else {
            $endDate = strtotime('+1 month', $startDate);
            $renewalDate = $endDate;
        }
        
        // Subscription einfügen
        $db->Query(
            'INSERT INTO {pre}user_subscriptions ' .
            '(user_id, product_id, product_name, status, start_date, end_date, renewal_date, billing_period, ' .
            'price, currency, order_id, grant_storage_gb, grant_domains, grant_users, grant_subdomains, grant_tenants, ' .
            'limit_email_in_mb, limit_email_out_mb, limit_aliases, limit_send_per_10min, limit_pop3_accounts, created_at) ' .
            'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())',
            $userID,
            $productID,
            $product['product_name'],
            'active',
            $startDate,
            $endDate,
            $renewalDate,
            $billingPeriod,
            $product['base_price'],
            $product['currency'],
            $orderID,
            $grants['storage_gb'],
            $grants['domains'],
            $grants['users'],
            $grants['subdomains'],
            $grants['tenants'],
            $limits['email_in_mb'],
            $limits['email_out_mb'],
            $limits['aliases'],
            $limits['send_per_10min'],
            $limits['pop3_accounts']
        );
        
        $subscriptionID = $db->InsertId();
        
        // User-Gruppe aktualisieren (falls nötig)
        self::UpdateUserGroup($userID, $product['target_group']);
        
        // User-Limits aktualisieren
        self::ApplyLimitsToUser($userID, $limits);
        
        return $subscriptionID;
    }
    
    /**
     * User-Gruppe auf Basis des Produkts aktualisieren
     */
    private static function UpdateUserGroup($userID, $targetGroup) {
        global $db;
        
        // Nur upgraden, nie downgraden
        $res = $db->Query('SELECT gruppe FROM {pre}users WHERE id=?', $userID);
        if($res->RowCount() > 0) {
            $user = $res->FetchArray(MYSQLI_ASSOC);
            if($user['gruppe'] < $targetGroup) {
                $db->Query('UPDATE {pre}users SET gruppe=? WHERE id=?', $targetGroup, $userID);
            }
            $res->Free();
        }
    }
    
    /**
     * Limits auf User anwenden
     */
    private static function ApplyLimitsToUser($userID, $limits) {
        global $db;
        
        $db->Query(
            'UPDATE {pre}users SET ' .
            'mailspace_add=?, mailspace_used=0, ' .
            'mail_limit_in=?, mail_limit_out=?, ' .
            'aliase=?, versand_limitiert=?, pop3_accounts=? ' .
            'WHERE id=?',
            $limits['email_in_mb'],
            $limits['email_in_mb'],
            $limits['email_out_mb'],
            $limits['aliases'],
            $limits['send_per_10min'],
            $limits['pop3_accounts'],
            $userID
        );
    }
    
    /**
     * Alle aktiven Subscriptions eines Users
     */
    public static function GetUserSubscriptions($userID) {
        global $db;
        
        $subscriptions = array();
        $res = $db->Query(
            'SELECT s.*, p.product_key FROM {pre}user_subscriptions s ' .
            'LEFT JOIN {pre}products p ON s.product_id = p.product_id ' .
            'WHERE s.user_id=? ORDER BY s.created_at DESC',
            $userID
        );
        while($row = $res->FetchArray(MYSQLI_ASSOC)) {
            $subscriptions[] = $row;
        }
        $res->Free();
        
        return $subscriptions;
    }
    
    /**
     * Subscription verlängern
     */
    public static function RenewSubscription($subscriptionID) {
        global $db;
        
        $res = $db->Query('SELECT * FROM {pre}user_subscriptions WHERE subscription_id=?', $subscriptionID);
        if($res->RowCount() == 0) {
            return false;
        }
        $sub = $res->FetchArray(MYSQLI_ASSOC);
        $res->Free();
        
        // Neue Laufzeit
        $newEndDate = $sub['billing_period'] == 'yearly' 
            ? strtotime('+1 year', $sub['end_date'])
            : strtotime('+1 month', $sub['end_date']);
        
        $newRenewalDate = $newEndDate;
        
        // Update
        $db->Query(
            'UPDATE {pre}user_subscriptions SET end_date=?, renewal_date=?, last_renewed=NOW() WHERE subscription_id=?',
            $newEndDate,
            $newRenewalDate,
            $subscriptionID
        );
        
        return true;
    }
    
    /**
     * Subscription kündigen
     */
    public static function CancelSubscription($subscriptionID) {
        global $db;
        
        $db->Query(
            'UPDATE {pre}user_subscriptions SET status=?, cancelled_at=NOW() WHERE subscription_id=?',
            'cancelled',
            $subscriptionID
        );
        
        return true;
    }
    
    /**
     * Abgelaufene Subscriptions finden
     */
    public static function FindExpiredSubscriptions() {
        global $db;
        
        $expired = array();
        $now = time();
        
        $res = $db->Query(
            'SELECT * FROM {pre}user_subscriptions WHERE status=? AND end_date < ?',
            'active',
            $now
        );
        while($row = $res->FetchArray(MYSQLI_ASSOC)) {
            $expired[] = $row;
        }
        $res->Free();
        
        return $expired;
    }
    
    /**
     * Subscription deaktivieren (nach Ablauf)
     */
    public static function DeactivateSubscription($subscriptionID) {
        global $db;
        
        $res = $db->Query('SELECT * FROM {pre}user_subscriptions WHERE subscription_id=?', $subscriptionID);
        if($res->RowCount() == 0) {
            return false;
        }
        $sub = $res->FetchArray(MYSQLI_ASSOC);
        $res->Free();
        
        // Status auf expired setzen
        $db->Query(
            'UPDATE {pre}user_subscriptions SET status=? WHERE subscription_id=?',
            'expired',
            $subscriptionID
        );
        
        // User-Limits zurücksetzen (auf Free-Plan oder so)
        // TODO: Definiere was passieren soll bei Ablauf
        
        return true;
    }
    
    /**
     * Subscription-Features für User abrufen
     */
    public static function GetUserFeatures($userID) {
        global $db;
        
        $features = array();
        
        // Alle aktiven Subscriptions
        $res = $db->Query(
            'SELECT DISTINCT f.* FROM {pre}user_subscriptions s ' .
            'INNER JOIN {pre}product_feature_map m ON s.product_id = m.product_id ' .
            'INNER JOIN {pre}product_features f ON m.feature_id = f.feature_id ' .
            'WHERE s.user_id=? AND s.status=? AND m.enabled=1 AND f.enabled=1',
            $userID,
            'active'
        );
        while($row = $res->FetchArray(MYSQLI_ASSOC)) {
            $features[$row['feature_key']] = $row;
        }
        $res->Free();
        
        return $features;
    }
    
    /**
     * Hat User ein bestimmtes Feature?
     */
    public static function HasFeature($userID, $featureKey) {
        $features = self::GetUserFeatures($userID);
        return isset($features[$featureKey]);
    }
}
?>
