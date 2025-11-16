<?php
/**
 * Paynow Callback Handler
 * 
 * This file handles callbacks from Paynow payment gateway
 */

define("CLIENTAREA", true);
require("../../../init.php");

use WHMCS\Database\Capsule;

// Get gateway configuration
$gatewaySettings = Capsule::table('tblpaymentgateways')
    ->where('gateway', 'paynow')
    ->pluck('value', 'setting')
    ->toArray();

$integrationId = isset($gatewaySettings['IntegrationID']) ? $gatewaySettings['IntegrationID'] : '';
$integrationKey = isset($gatewaySettings['IntegrationKey']) ? $gatewaySettings['IntegrationKey'] : '';

if (empty($integrationId) || empty($integrationKey)) {
    die("Paynow gateway not configured");
}

// Include Paynow SDK
$sdkPath = dirname(__FILE__) . '/../../paynow-sdk/autoloader.php';

if (!file_exists($sdkPath)) {
    die("Paynow SDK not found");
}

require_once $sdkPath;

try {
    // Initialize Paynow
    $paynow = new Paynow\Payments\Paynow(
        $integrationId,
        $integrationKey,
        '',
        ''
    );
    
    // Process status update
    $status = $paynow->processStatusUpdate();
    
    // Get transaction reference (invoice ID)
    $reference = $status->reference();
    $paynowReference = $status->paynowReference();
    $amount = $status->amount();
    $transactionStatus = $status->status();
    
    // Find invoice by reference
    $invoice = Capsule::table('tblinvoices')
        ->where('id', $reference)
        ->first();
    
    if (!$invoice) {
        die("Invoice not found");
    }
    
    // Check if payment was successful
    if ($status->paid()) {
        // Add payment to invoice
        $command = 'AddInvoicePayment';
        $postData = array(
            'invoiceid' => $reference,
            'transid' => $paynowReference,
            'amount' => $amount,
            'gateway' => 'paynow'
        );
        
        $results = localAPI($command, $postData);
        
        // Redirect to invoice page
        header('Location: ' . $CONFIG['SystemURL'] . '/viewinvoice.php?id=' . $reference . '&paymentsuccess=true');
        exit;
    } else {
        // Payment failed or pending
        header('Location: ' . $CONFIG['SystemURL'] . '/viewinvoice.php?id=' . $reference . '&paymentfailed=true');
        exit;
    }
    
} catch (Exception $e) {
    // Log error
    logActivity('Paynow Callback Error: ' . $e->getMessage());
    
    // Redirect to invoice page with error
    if (isset($_POST['reference'])) {
        header('Location: ' . $CONFIG['SystemURL'] . '/viewinvoice.php?id=' . $_POST['reference'] . '&paymentfailed=true');
    } else {
        header('Location: ' . $CONFIG['SystemURL'] . '/clientarea.php');
    }
    exit;
}

