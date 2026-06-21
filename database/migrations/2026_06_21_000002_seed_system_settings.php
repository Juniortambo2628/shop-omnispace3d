<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $settings = [
            'bank_transfer_details' => "Account Name: Omnispace (K) Ltd\nBank Details: NCBA, Upper Hill Branch, Mara Road, Upper Hill P.O. Box 44599 - 00100, Nairobi, KENYA\nAcct Number: 8057990042 (USD)\nBank ID: 07000, Branch Code: 600\nSWIFT CODE: CBAFKENX",
            'paypal_payment_link' => 'https://paypal.me/omnispace3d',
            'payment_portal_url' => 'https://payments.omnispace3d.com',
            'company_pin' => 'P051234567A',
            'invoice_disclaimer_text' => "ORDER FULFILLMENT INSTRUCTIONS
A copy of this order form completed, signed, stamped and dated constitutes a valid ORDER
HOWEVER NO ORDER IS COMPLETE UNTIL FULL PAYMENT IS RECEIVED AND PROCESSED 
(i) Upon receipt of the completed Order Form, our team will check the order for completeness and, if order is correct and all items available we will send you a countersigned order form with Invoice number
(ii) Payment must be made within 10 days of issuance of the invoice in order to guarantee availability of products and services. We will not hold product/guarantee product availability for more than 10 days
(iii) FULL payment must be received for the order to be complete. Of note partial payments will not constitute a completed order
(iv) Orders will be processed on a first-come-first-served basis
(v) Cancelled orders may incur a charge for up to 100% of total costs
(vi) Please note that orders will only be processed if payment is received prior to opening of the show
Other Notes.
All prices are for RENTAL of goods and services and are inclusive of applicable local taxes, delivery and set up costs
Prices EXCLUDE auxiliary services not specifically mentioned in the description eg electrical outlets
Please note that your order is only processed if payment is received prior to opening of the show
Upon receipt of the form, our team will check the order for completeness and send you an official invoice with payment information
At the onsite Service Desk we are unable to accept cash for orders placed on site
While every effort is made to ensure an exact color match, we cannot be responsible for varied color supplied due to batch production
SUBSTITUTION
Supplier may from time to time make minor/non-material substitutions with comparable product. Any major substitutions (color, style, functionality) will be communicated to the Client, and signed off prior to substitutions being made
CANCELLATION
There will be NO cancellations accepted once an order is complete. However the Client may apply for a credit towards another product/service up to 2 months in advance of delivery for certain products and up to 1 month ahead of delivery for certain services
PAYMENT TERMS
Payments should be made via EFT or Wire Transfer to:
Account Name: Omnispace (K) Ltd
Bank Details: NCBA, Upper Hill Branch, Mara Road, Upper Hill P.O. Box 44599 - 00100, Nairobi, KENYA
Acct Number: 8057990042 (USD)
Bank ID: 07000, Branch Code: 600
SWIFT CODE: CBAFKENX",
            'bank_charge_warning_text' => "ALL BANK CHARGES TO BE BORNE BY SENDER
Please note, the amount credited into our bank must be the exact order value and any shortage must be paid in full prior to any order being processed. Telex Transfers which have not been received and cleared through our bank prior to the build up of the show will be regarded as unpaid and the service will not be supplied until the outstanding amount is paid in full. Please ensure therefore that you allow adequate time for your telex transfer to clear the banking system to avoid disappointment upon arrival on site. Special payment methods (eg Bankers Check) may be negotiated on a case by case basis. However orders will only be considered complete once payment is received in full",
            'email_template_new_order' => '<p>Dear {contact_name},</p><p>Thank you for your order <strong>{order_id}</strong>. We have received it and our team will review it shortly.</p><p>Your order is currently <span style="color:#F59E0B;font-weight:bold;">Pending Review</span>.</p><p>Our team will check availability and confirm your order within 24 hours. You will receive a follow-up email once availability is confirmed with payment instructions.</p><p>Order Summary:<br>{items_table}<br>{totals_block}</p><p>If you have any questions, please contact us at {company_email}.</p>',
            'email_template_availability_confirmed' => '<p>Hi {contact_name},</p><p>Great news! Availability has been confirmed for your order <strong>{order_id}</strong>.</p><p>Please proceed to payment using one of the following methods:</p><ul><li><strong>PayPal:</strong> <a href="{paypal_link}">{paypal_link}</a></li><li><strong>Bank Transfer:</strong> See invoice attached for full bank details</li><li><strong>Payment Portal:</strong> <a href="{payment_portal_url}">{payment_portal_url}</a></li></ul><p><strong>Important:</strong> Payment must be made within 10 days to guarantee availability.</p><p>{items_table}<br>{totals_block}</p><p>Your tax invoice is attached. Please reference <strong>{order_id}</strong> on all payments.</p>',
            'email_template_payment_processed' => '<p>Dear {contact_name},</p><p>Thank you! Your payment for order <strong>{order_id}</strong> has been received and processed.</p><p><strong>Order Complete</strong> — Your booth <strong>{booth_number}</strong> is now confirmed.</p><p><strong>Billing Summary:</strong><br>{items_table}<br>{totals_block}</p><p>Your tax invoice is attached for your records.</p><p>We look forward to serving you at the event. If you need any further assistance, please contact us at {company_email} or {company_phone}.</p>',
            'invoice_signatory_left_title' => 'Customer Approval',
            'invoice_signatory_right_title' => 'Omnispace Verification',
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $keys = [
            'bank_transfer_details',
            'paypal_payment_link',
            'payment_portal_url',
            'company_pin',
            'invoice_disclaimer_text',
            'bank_charge_warning_text',
            'email_template_new_order',
            'email_template_availability_confirmed',
            'email_template_payment_processed',
            'invoice_signatory_left_title',
            'invoice_signatory_right_title',
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
    }
};