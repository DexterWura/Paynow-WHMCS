# Paynow Payment Gateway for WHMCS

A complete payment gateway integration module for WHMCS that allows you to accept payments through Paynow Zimbabwe.

**Author:** DEXTERITY wurayayi  
**Version:** 1.0.0

> **Important:** This module was developed by an independent developer. For issues related to the Paynow payment gateway service itself, please contact Paynow directly at support@paynow.co.zw.

## Features

- ✅ Full integration with Paynow Zimbabwe payment gateway
- ✅ Secure payment processing
- ✅ Automatic invoice payment updates
- ✅ Support for all Paynow payment methods
- ✅ Easy installation and configuration
- ✅ WHMCS 7.x and 8.x compatible

## Requirements

- WHMCS 7.0 or higher
- PHP 5.6 or higher
- cURL extension enabled
- Valid Paynow Zimbabwe merchant account with Integration ID and Integration Key

## Installation

### Step 1: Upload Files to WHMCS

1. **Copy the entire `modules` folder** from this package to your WHMCS installation root directory.

   **Example:**
   ```
   Your WHMCS installation: /var/www/whmcs/
   Copy: modules/ → /var/www/whmcs/modules/
   ```

2. **Verify file permissions**: Ensure the files are readable by your web server.
   - Directories: `755` (drwxr-xr-x)
   - PHP files: `644` (-rw-r--r--)

3. **Verify file structure** after uploading:
   ```
   /whmcs/
   ├── modules/
   │   ├── gateways/
   │   │   ├── paynow.php
   │   │   └── callback/
   │   │       └── paynow.php
   │   └── paynow-sdk/
   │       ├── autoloader.php
   │       ├── src/
   │       │   ├── Core/
   │       │   ├── Http/
   │       │   ├── Payments/
   │       │   └── Util/
   │       └── ...
   ```

### Step 2: Configure the Gateway in WHMCS

1. Log in to your WHMCS admin area.

2. Navigate to **Setup → Payments → Payment Gateways**.

3. In the "Active Payment Gateways" section, find **Paynow Zimbabwe** and click **Activate**.

4. Click **Configure** next to Paynow Zimbabwe.

5. Enter your Paynow credentials:
   - **Integration ID**: Your Paynow Integration ID (obtained from your Paynow merchant dashboard)
   - **Integration Key**: Your Paynow Integration Key (obtained from your Paynow merchant dashboard)
   - **Test Mode**: Enable this for testing purposes (disable for production)

6. Click **Save Changes**.

### Step 3: Get Your Paynow Credentials

If you don't have your Paynow Integration ID and Integration Key:

1. Log in to your [Paynow Merchant Dashboard](https://www.paynow.co.zw)
2. Navigate to **Settings → Integration**
3. Copy your **Integration ID** and **Integration Key**
4. Paste them into the WHMCS gateway configuration

### Step 4: Test the Integration

1. Create a test invoice in WHMCS.
2. Attempt to pay the invoice using Paynow.
3. Complete a test payment (use Paynow's test mode if available).
4. Verify that the invoice is automatically marked as paid in WHMCS.

## File Structure

```
Paynow-WHMCS/
├── modules/
│   ├── gateways/
│   │   ├── paynow.php                    # Main WHMCS gateway module
│   │   └── callback/
│   │       └── paynow.php                # Callback handler for Paynow
│   └── paynow-sdk/                        # Paynow PHP SDK
│       ├── autoloader.php                # SDK autoloader
│       ├── src/
│       │   ├── Core/                     # Core SDK classes
│       │   ├── Http/                     # HTTP client classes
│       │   ├── Payments/                 # Payment processing classes
│       │   └── Util/                     # Utility classes
│       └── ...
└── README.md                              # This file
```

### Key Files

**Gateway Module (`modules/gateways/paynow.php`)**
- Main WHMCS payment gateway module
- Functions:
  - `paynow_config()`: Defines gateway configuration fields
  - `paynow_link()`: Generates payment button/link
  - `paynow_callback()`: Processes payment callbacks

**Callback Handler (`modules/gateways/callback/paynow.php`)**
- Handles callbacks from Paynow payment gateway
- Processes payment status updates and updates WHMCS invoices automatically

**SDK (`modules/paynow-sdk/`)**
- Paynow PHP SDK for communicating with Paynow API
- Required by both gateway module and callback handler

## How It Works

1. **Payment Initiation**: When a client clicks "Pay Invoice", WHMCS calls the `paynow_link()` function which:
   - Creates a payment request using the Paynow SDK
   - Sends the payment details to Paynow
   - Returns a payment button that redirects the client to Paynow

2. **Payment Processing**: The client is redirected to Paynow where they complete the payment using their preferred method.

3. **Callback Processing**: After payment, Paynow sends a callback to your WHMCS installation:
   - The callback handler (`callback/paynow.php`) receives the payment status
   - It verifies the payment using the Paynow SDK
   - If payment is successful, it automatically adds the payment to the invoice in WHMCS
   - The client is redirected back to the invoice page

## Configuration Options

### Integration ID
Your unique Paynow merchant Integration ID. This is required for all transactions.

### Integration Key
Your Paynow Integration Key used for secure communication. Keep this confidential. **Note: The Integration Key is case-sensitive.**

### Test Mode
Enable this option to test payments without processing real transactions. **Remember to disable this in production!**

## Troubleshooting

### Gateway doesn't appear in WHMCS
- Check file permissions (755 for directories, 644 for files)
- Verify all files were uploaded correctly
- Clear WHMCS cache if applicable
- Ensure the `modules/gateways/paynow.php` file exists

### Payment button not showing
- Verify that the SDK files are correctly uploaded to `modules/paynow-sdk/`
- Check file permissions (files should be readable)
- Ensure Integration ID and Integration Key are correctly entered
- Check WHMCS error logs for detailed error messages

### Payments not updating automatically
- Verify that your server can receive callbacks from Paynow
- Check that the callback URL is accessible: `https://yourdomain.com/modules/gateways/callback/paynow.php`
- Review WHMCS activity log for any error messages
- Ensure your Integration Key is correct (case-sensitive)
- Check server firewall allows incoming connections from Paynow

### "SDK not found" error
- Verify that `modules/paynow-sdk/autoloader.php` exists
- Check file paths and permissions
- Ensure all SDK files were uploaded correctly

### Callback verification fails
- Double-check your Integration Key (it's case-sensitive)
- Ensure your server's system time is synchronized
- Check WHMCS error logs for detailed error messages
- Verify the callback URL is accessible from external sources

## Security Notes

- **Never share your Integration Key** with anyone
- Keep your WHMCS installation and all modules up to date
- Use HTTPS for your WHMCS installation
- Regularly review payment transactions in your Paynow dashboard
- Disable Test Mode in production
- Keep your Paynow credentials secure and never commit them to version control

## Testing Checklist

Before going live:

1. ✅ Test with a small amount first
2. ✅ Verify that invoices are automatically marked as paid
3. ✅ Test the callback functionality
4. ✅ Verify payment records appear correctly in WHMCS
5. ✅ Check that payment notifications are sent to clients
6. ✅ Disable Test Mode before accepting real payments
7. ✅ Verify callback URL is accessible from external sources

## Support

### For Paynow Gateway Issues
**Contact Paynow directly:**
- Email: support@paynow.co.zw
- Website: https://www.paynow.co.zw
- Merchant Dashboard: https://www.paynow.co.zw

> **Note:** This module was developed by an independent developer. For issues related to the Paynow payment gateway service, API, or merchant account, please contact Paynow support directly.

### For WHMCS Issues
- Contact WHMCS support or check WHMCS documentation
- WHMCS Documentation: https://developers.whmcs.com/payment-gateways/

### For This Module
- Review the troubleshooting section above
- Check WHMCS error logs
- Verify all files are correctly uploaded and have proper permissions

## Version History

- **1.0.0** (Initial Release)
  - Full Paynow integration
  - Automatic invoice payment updates
  - Secure callback handling
  - WHMCS 7.x and 8.x support

## License

This module is provided as-is for use with WHMCS and Paynow Zimbabwe. Please ensure you comply with both WHMCS and Paynow terms of service.

## Additional Resources

- [Paynow Developer Documentation](https://www.paynow.co.zw/support)
- [WHMCS Payment Gateway Documentation](https://developers.whmcs.com/payment-gateways/)
- [Paynow Merchant Dashboard](https://www.paynow.co.zw)

---

**Author:** DEXTERITY wurayayi  
**Disclaimer:** This module was developed by an independent developer. For issues with the Paynow payment gateway service, please contact Paynow directly at support@paynow.co.zw.
