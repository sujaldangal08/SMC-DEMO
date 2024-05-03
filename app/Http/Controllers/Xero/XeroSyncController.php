<?php

namespace App\Http\Controllers\Xero;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Xero\Address;
use App\Models\Xero\Contact;
use App\Models\Xero\LineItem;
use App\Models\Xero\Phone;
use App\Models\Xero\PurchaseOrder;
use App\Models\Xero\XeroConnect;
use App\Models\Xero\XeroTenant;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class XeroSyncController extends Controller
{
    public function syncContacts()
    {
        $token = XeroConnect::get()->first();
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first();
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
        $token = XeroConnect::get()->first();
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first();
        $tenantId = $tenant->tenantId;
        $contact = Contact::get()->first();

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
        $token = XeroConnect::get()->first();
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first();
        $tenantId = $tenant->tenantId;
        $contact = Contact::get()->first();

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


        foreach ($purchaseOrders as $xeroPurchaseOrders) {
            $purchaseOrder = PurchaseOrder::updateOrCreate(
                ['purchase_order_id' => $xeroPurchaseOrders['PurchaseOrderID']],
                [
                    'purchase_order_number' => $xeroPurchaseOrders['PurchaseOrderNumber'],
                    'date' => date('Y-m-d H:i:s', explode('+', str_replace(['/Date(', ')/'], '', $xeroPurchaseOrders['Date']))[0] / 1000),
                    'delivery_address' => $xeroPurchaseOrders['DeliveryAddress'],
                    'attention_to' => $xeroPurchaseOrders['AttentionTo'],
                    'telephone' => $xeroPurchaseOrders['Telephone'],
                    'delivery_instructions' => $xeroPurchaseOrders['DeliveryInstructions'],
                    'has_errors' => $xeroPurchaseOrders['HasErrors'],
                    'is_discounted' => $xeroPurchaseOrders['IsDiscounted'],
                    'reference' => $xeroPurchaseOrders['Reference'],
                    'type' => $xeroPurchaseOrders['Type'],
                    'currency_rate' => $xeroPurchaseOrders['CurrencyRate'],
                    'currency_code' => $xeroPurchaseOrders['CurrencyCode'],
                    'contact_id' => $contact->id,
                    'branding_theme_id' => $xeroPurchaseOrders['BrandingThemeID'],
                    'status' => $xeroPurchaseOrders['Status'],
                    'line_amount_types' => $xeroPurchaseOrders['LineAmountTypes'],
                    'sub_total' => $xeroPurchaseOrders['SubTotal'],
                    'total_tax' => $xeroPurchaseOrders['TotalTax'],
                    'total' => $xeroPurchaseOrders['Total'],
                    'updated_date_utc' => date('Y-m-d H:i:s', explode('+', str_replace(['/Date(', ')/'], '', $xeroPurchaseOrders['UpdatedDateUTC']))[0] / 1000),
                    'has_attachments' => $xeroPurchaseOrders['HasAttachments'],
                ]
            );

            foreach ($xeroPurchaseOrders['LineItems'] as $lineItemData) {
                $lineItem = LineItem::updateOrCreate(
                    ['line_item_id' => $lineItemData['LineItemID']],
                    [
                        'description' => $lineItemData['Description'],
                        'unit_amount' => $lineItemData['UnitAmount'],
                        'tax_type' => $lineItemData['TaxType'],
                        'tax_amount' => $lineItemData['TaxAmount'],
                        'line_amount' => $lineItemData['LineAmount'],
                        'quantity' => $lineItemData['Quantity'],
                        'purchase_order_id' => $purchaseOrder->id,
                    ]
                );
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
