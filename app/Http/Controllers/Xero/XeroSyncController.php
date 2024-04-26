<?php

namespace App\Http\Controllers\Xero;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Xero\Address;
use App\Models\Xero\Contact;
use App\Models\Xero\Phone;
use App\Models\Xero\XeroConnect;
use App\Models\Xero\XeroTenant;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\Xero\PurchaseOrder;
use App\Models\Xero\LineItem;

class XeroSyncController extends Controller
{
    public function syncContacts()
    {
        $token = XeroConnect::get()->first(); // Replace with your actual access token
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first(); // Replace with your actual tenant
        $tenantId = $tenant->tenantId;

        $client = new Client();

        try {
            $response = $client->request('GET', uri: 'https://api.xero.com/api.xro/2.0/Contacts', options: [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Xero-tenant-id' => $tenantId,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
            $contacts = json_decode($response->getBody()->getContents(), true)['Contacts'];
            foreach ($contacts as $xeroContact) {
                $contact = new Contact();
                $contact->contact_id = $xeroContact['ContactID'];
                $contact->contact_status = $xeroContact['ContactStatus'];
                $contact->name = $xeroContact['Name'];
                $contact->is_supplier = $xeroContact['IsSupplier'];
                $contact->is_customer = $xeroContact['IsCustomer'];
                $contact->has_attachments = $xeroContact['HasAttachments'];
                $contact->has_validation_errors = $xeroContact['HasValidationErrors'];
                $contact->save();
                // Save the addresses

                // Store Phones
                foreach ($xeroContact['Phones'] as $phoneData) {
                    $phone = new Phone();
                    $phone->address_type = $phoneData['PhoneType'];
                    $phone->contact_id = $contact->id;
                    $contact->phones()->save($phone);
                }

                // Store Addresses
                foreach ($xeroContact['Addresses'] as $addressData) {
                    $address = new Address();
                    $address->address_type = $addressData['AddressType'];
                    $address->contact_id = $contact->id;
                    $contact->addresses()->save($address);
                }
            }

            return response()->json(['message' => 'Contacts synced successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        } catch (GuzzleException $e) {
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        }
    }

    public function syncInvoices()
    {
        $token = XeroConnect::get()->first(); // Replace with your actual access token
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first(); // Replace with your actual tenant
        $tenantId = $tenant->tenantId;
        $contact = Contact::get()->first(); // Replace with your actual contact

        $client = new Client();

        try {
            $response = $client->request('GET', uri: 'https://api.xero.com/api.xro/2.0/Invoices', options: [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Xero-tenant-id' => $tenantId,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
            $invoices = json_decode($response->getBody()->getContents(), true)['Invoices'];
            foreach ($invoices as $xeroInvoice) {
                $invoice = new SalesOrder();
                $invoice->invoice_id = $xeroInvoice['InvoiceID'];
                $invoice->invoice_number = $xeroInvoice['InvoiceNumber'];
                $invoice->reference = $xeroInvoice['Reference'];
                $invoice->amount_due = $xeroInvoice['AmountDue'];
                $invoice->amount_paid = $xeroInvoice['AmountPaid'];
                $invoice->amount_credited = $xeroInvoice['AmountCredited'];
                $invoice->contact_id = $contact->id; // Assuming the ContactID is available in the Contact object of the invoice
                $invoice->save();
            }

            return response()->json(['message' => 'Invoices synced successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        } catch (GuzzleException $e) {
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        }
    }

    public function syncPurchaseOrders()
    {
        $token = XeroConnect::get()->first(); // Replace with your actual access token
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first(); // Replace with your actual tenant
        $tenantId = $tenant->tenantId;
        $contact = Contact::get()->first(); // Replace with your actual contact

        $client = new Client();

        try {
            $response = $client->request('GET', uri: 'https://api.xero.com/api.xro/2.0/PurchaseOrders', options: [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Xero-tenant-id' => $tenantId,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
            $purchaseOrders = json_decode($response->getBody()->getContents(), true)['PurchaseOrders'];

            foreach($purchaseOrders as $xeroPurchaseOrders){
                $purchaseOrder = new PurchaseOrder();
                $purchaseOrder->purchase_order_id = $xeroPurchaseOrders['PurchaseOrderID'];
                $purchaseOrder->purchase_order_number = $xeroPurchaseOrders['PurchaseOrderNumber'];

                // Convert 'Date' and 'DeliveryDate'
                $timestamp = str_replace(['/Date(', ')/'], '', $xeroPurchaseOrders['Date']);
                $parts = explode('+', $timestamp);
                $purchaseOrder->date = date('Y-m-d H:i:s', $parts[0] / 1000);

                // $timestamp = str_replace(['/Date(', ')/'], '', $xeroPurchaseOrders['DeliveryDate']);
                // $parts = explode('+', $timestamp);
                // $purchaseOrder->delivery_date = date('Y-m-d H:i:s', $parts[0] / 1000);
                $purchaseOrder->delivery_address = $xeroPurchaseOrders['DeliveryAddress'];
                $purchaseOrder->attention_to = $xeroPurchaseOrders['AttentionTo'];
                $purchaseOrder->telephone = $xeroPurchaseOrders['Telephone'];
                $purchaseOrder->delivery_instructions = $xeroPurchaseOrders['DeliveryInstructions'];
                $purchaseOrder->has_errors = $xeroPurchaseOrders['HasErrors'];
                $purchaseOrder->is_discounted = $xeroPurchaseOrders['IsDiscounted'];
                $purchaseOrder->reference = $xeroPurchaseOrders['Reference'];
                $purchaseOrder->type = $xeroPurchaseOrders['Type'];
                $purchaseOrder->currency_rate = $xeroPurchaseOrders['CurrencyRate'];
                $purchaseOrder->currency_code = $xeroPurchaseOrders['CurrencyCode'];
                $purchaseOrder->contact_id = $contact->id;
                $purchaseOrder->branding_theme_id = $xeroPurchaseOrders['BrandingThemeID'];
                $purchaseOrder->status = $xeroPurchaseOrders['Status'];
                $purchaseOrder->line_amount_types = $xeroPurchaseOrders['LineAmountTypes'];
                $purchaseOrder->sub_total = $xeroPurchaseOrders['SubTotal'];
                $purchaseOrder->total_tax = $xeroPurchaseOrders['TotalTax'];
                $purchaseOrder->total = $xeroPurchaseOrders['Total'];

                $timestamp = str_replace(['/Date(', ')/'], '', $xeroPurchaseOrders['UpdatedDateUTC']);
                $parts = explode('+', $timestamp);
                $purchaseOrder->updated_date_utc = date('Y-m-d H:i:s', $parts[0] / 1000);

                $purchaseOrder->has_attachments = $xeroPurchaseOrders['HasAttachments'];
                $purchaseOrder->save();

                foreach ($xeroPurchaseOrders['LineItems'] as $lineItemData) {
                    $lineItem = new LineItem();
                    // $lineItem->id = \Illuminate\Support\Str::uuid(); // Generate a UUID for the id
                    $lineItem->description = $lineItemData['Description'];
                    $lineItem->unit_amount = $lineItemData['UnitAmount'];
                    $lineItem->tax_type = $lineItemData['TaxType'];
                    $lineItem->tax_amount = $lineItemData['TaxAmount'];
                    $lineItem->line_amount = $lineItemData['LineAmount'];
                    $lineItem->quantity = $lineItemData['Quantity'];
                    $lineItem->purchase_order_id = $purchaseOrder->id;
                    $purchaseOrder->lineItems()->save($lineItem);
                }

            }
            return response()->json(['message' => 'Purchase Orders synced successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        } catch (GuzzleException $e) {
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        }
    }


}
