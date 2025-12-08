<?php
/**
 * b1gMail Product Subscription Plugin
 * Hook für BMPayment - Aktiviert Subscriptions nach erfolgreicher Zahlung
 */

class ProductSubscriptionPlugin extends BMPlugin {
    
    function __construct() {
        $this->type = BMPLUGIN_DEFAULT;
        $this->name = 'Product Subscription Manager';
        $this->author = 'b1gMail';
        $this->version = '1.0.0';
        $this->description = 'Aktiviert Product Subscriptions nach erfolgreicher Zahlung';
    }
    
    /**
     * OnActivateOrder Hook
     * Wird aufgerufen wenn eine Order aktiviert wird (nach Zahlung)
     */
    function OnActivateOrder($orderID) {
        global $db;
        
        // Subscription-Klasse laden
        require_once(B1GMAIL_DIR . 'serverlib/subscription.class.php');
        
        // Order-Items prüfen
        $res = $db->Query('SELECT * FROM {pre}orderitems WHERE orderid=?', $orderID);
        $hasSubscription = false;
        
        while($item = $res->FetchArray(MYSQLI_ASSOC)) {
            if($item['type'] == 'product_subscription') {
                $hasSubscription = true;
                break;
            }
        }
        $res->Free();
        
        // Nur wenn Order Subscriptions enthält
        if(!$hasSubscription) {
            return;
        }
        
        // Subscription aktivieren
        $result = BMSubscription::ActivateFromOrder($orderID);
        
        if($result) {
            PutLog(
                sprintf('Product Subscription: Order #%d aktiviert', $orderID),
                PRIO_NOTE,
                0,
                'product-subscription'
            );
        } else {
            PutLog(
                sprintf('Product Subscription: Fehler bei Order #%d', $orderID),
                PRIO_WARNING,
                0,
                'product-subscription'
            );
        }
    }
    
    /**
     * User Preferences Page Handler
     * Zeigt Subscriptions im User-Bereich
     */
    function UserPrefsPageHandler($action) {
        global $tpl, $db, $currentUser;
        
        if($action != 'subscriptions') {
            return false;
        }
        
        require_once(B1GMAIL_DIR . 'serverlib/subscription.class.php');
        
        $userID = $currentUser['id'];
        $subscriptions = BMSubscription::GetUserSubscriptions($userID);
        
        // Template-Variablen
        $tpl->assign('subscriptions', $subscriptions);
        $tpl->assign('pageURL', $this->_pageURL());
        
        // Template anzeigen
        $tpl->display('product-subscriptions.user.tpl');
        
        return true;
    }
}

// Plugin registrieren
$plugins->registerPlugin('ProductSubscriptionPlugin');
?>
