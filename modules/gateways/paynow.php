<?php
/**
 * Paynow Payment Gateway Module for WHMCS
 * 
 * This module integrates Paynow Zimbabwe payment gateway with WHMCS
 * 
 * @author DEXTERITY wurayayi
 * @version 1.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Gateway configuration fields
 * 
 * @return array
 */
function paynow_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Paynow Zimbabwe'
        ),
        'IntegrationID' => array(
            'FriendlyName' => 'Integration ID',
            'Type' => 'text',
            'Size' => '50',
            'Description' => 'Enter your Paynow Integration ID'
        ),
        'IntegrationKey' => array(
            'FriendlyName' => 'Integration Key',
            'Type' => 'password',
            'Size' => '50',
            'Description' => 'Enter your Paynow Integration Key'
        ),
        'TestMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Enable test mode for testing payments'
        )
    );
}

/**
 * Generate payment link
 * 
 * @param array $params Gateway parameters
 * @return string
 */
function paynow_link($params)
{
    // Include Paynow SDK
    $sdkPath = dirname(__FILE__) . '/../paynow-sdk/autoloader.php';
    
    if (!file_exists($sdkPath)) {
        return '<div class="alert alert-danger">Paynow SDK not found. Please ensure the SDK files are properly installed.</div>';
    }
    
    require_once $sdkPath;
    
    // Get configuration
    $integrationId = $params['IntegrationID'];
    $integrationKey = $params['IntegrationKey'];
    
    // Validate credentials
    if (empty($integrationId) || empty($integrationKey)) {
        return '<div class="alert alert-danger">Paynow credentials are not configured. Please contact support.</div>';
    }
    
    // Get invoice details
    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];
    $currency = $params['currency'];
    $description = $params['description'];
    $clientEmail = $params['clientdetails']['email'];
    $clientName = $params['clientdetails']['firstname'] . ' ' . $params['clientdetails']['lastname'];
    
    // Build return and result URLs
    $systemUrl = rtrim($params['systemurl'], '/');
    $returnUrl = $systemUrl . '/modules/gateways/callback/paynow.php';
    $resultUrl = $systemUrl . '/modules/gateways/callback/paynow.php';
    
    try {
        // Initialize Paynow
        $paynow = new Paynow\Payments\Paynow(
            $integrationId,
            $integrationKey,
            $returnUrl,
            $resultUrl
        );
        
        // Create payment
        $payment = $paynow->createPayment($invoiceId, $clientEmail);
        
        // Add invoice item
        $payment->add($description, $amount);
        
        // Send payment to Paynow
        $response = $paynow->send($payment);
        
        if ($response->success()) {
            // Get redirect URL
            $redirectUrl = $response->redirectUrl();
            
            // Store poll URL in session or database for status checking
            $pollUrl = $response->pollUrl();
            
            // Store poll URL in invoice notes or custom field for later retrieval
            // This is important for status checking
            
            // Generate payment button
            $code = '<form method="post" action="' . $redirectUrl . '">';
            $code .= '<input type="submit" value="Pay with Paynow" class="btn btn-primary" />';
            $code .= '</form>';
            
            // Store poll URL in invoice notes for callback processing (optional)
            // The callback will work without this as Paynow sends the reference in the callback
            
            return $code;
        } else {
            $error = $response->data();
            $errorMessage = isset($error['error']) ? $error['error'] : 'An error occurred while processing your payment request.';
            return '<div class="alert alert-danger">Payment Error: ' . htmlspecialchars($errorMessage) . '</div>';
        }
        
    } catch (Exception $e) {
        return '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

/**
 * Handle callback from Paynow
 * 
 * @param array $params Gateway parameters
 * @return array
 */
function paynow_callback($params)
{
    // Include Paynow SDK
    $sdkPath = dirname(__FILE__) . '/../paynow-sdk/autoloader.php';
    
    if (!file_exists($sdkPath)) {
        return array(
            'status' => 'error',
            'rawdata' => 'SDK not found'
        );
    }
    
    require_once $sdkPath;
    
    // Get configuration
    $integrationId = $params['IntegrationID'];
    $integrationKey = $params['IntegrationKey'];
    
    try {
        // Initialize Paynow
        $paynow = new Paynow\Payments\Paynow(
            $integrationId,
            $integrationKey,
            '',
            ''
        );
        
        // Process status update from Paynow
        $status = $paynow->processStatusUpdate();
        
        // Get transaction details
        $reference = $status->reference(); // This is the invoice ID
        $paynowReference = $status->paynowReference();
        $amount = $status->amount();
        $transactionStatus = $status->status();
        
        // Check if payment was successful
        if ($status->paid()) {
            return array(
                'status' => 'success',
                'transid' => $paynowReference,
                'rawdata' => json_encode($status->data()),
                'fees' => 0.00
            );
        } else {
            return array(
                'status' => $transactionStatus,
                'transid' => $paynowReference,
                'rawdata' => json_encode($status->data())
            );
        }
        
    } catch (Exception $e) {
        return array(
            'status' => 'error',
            'rawdata' => 'Error: ' . $e->getMessage()
        );
    }
}

