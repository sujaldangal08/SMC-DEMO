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
use Illuminate\Http\JsonResponse;

class XeroSyncController extends Controller
{
    /**
     * This method is responsible for syncing contacts from Xero.
     * It first retrieves the access token and tenant ID from the database.
     * Then, it makes a GET request to the Xero API to retrieve all contacts.
     * For each contact, it creates a new Contact record in the database.
     * It also saves the associated phone numbers and addresses for each contact.
     * If an exception occurs during the process, it returns a JSON response with the error message.
     *
     * @return JsonResponse
     */
    public function syncContacts()
    {
        // Retrieve the access token and tenant ID from the database
        $token = XeroConnect::get()->first();
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first();
        $tenantId = $tenant->tenantId;

        // Initialize a new Guzzle HTTP client
        $client = new Client();

        try {
            // Make a GET request to the Xero API to retrieve all contacts
            $response = $client->request('GET', uri: 'https://api.xero.com/api.xro/2.0/Contacts', options: [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Xero-tenant-id' => $tenantId,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            // Decode the response body into an associative array
            $contacts = json_decode($response->getBody()->getContents(), true)['Contacts'];

            // For each contact, create a new Contact record in the database
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

            // Return a success message
            return response()->json(['message' => 'Contacts synced successfully']);
        } catch (\Exception $e) {
            // If an exception occurs, return a JSON response with the error message
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        } catch (GuzzleException $e) {
            // If a GuzzleException occurs, return a JSON response with the error message
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * This method is responsible for syncing invoices from Xero.
     * It first retrieves the access token and tenant ID from the database.
     * Then, it makes a GET request to the Xero API to retrieve all invoices.
     * For each invoice, it creates a new SalesOrder record in the database.
     * If an exception occurs during the process, it returns a JSON response with the error message.
     *
     * @return JsonResponse
     */
    public function syncInvoices()
    {
        // Retrieve the access token and tenant ID from the database
        $token = XeroConnect::get()->first();
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first();
        $tenantId = $tenant->tenantId;
        $contact = Contact::get()->first();

        // Initialize a new Guzzle HTTP client
        $client = new Client();

        try {
            // Make a GET request to the Xero API to retrieve all invoices
            $response = $client->request('GET', uri: 'https://api.xero.com/api.xro/2.0/Invoices', options: [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Xero-tenant-id' => $tenantId,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            // Decode the response body into an associative array
            $invoices = json_decode($response->getBody()->getContents(), true)['Invoices'];

            // For each invoice, create a new SalesOrder record in the database
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

            // Return a success message
            return response()->json(['message' => 'Invoices synced successfully']);
        } catch (\Exception $e) {
            // If an exception occurs, return a JSON response with the error message
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        } catch (GuzzleException $e) {
            // If a GuzzleException occurs, return a JSON response with the error message
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * This method is responsible for syncing purchase orders from Xero.
     * It first retrieves the access token and tenant ID from the database.
     * Then, it makes a GET request to the Xero API to retrieve all purchase orders.
     * For each purchase order, it creates or updates a PurchaseOrder record in the database.
     * It also saves the associated line items for each purchase order.
     * If an exception occurs during the process, it returns a JSON response with the error message.
     *
     * @return JsonResponse
     */
    public function syncPurchaseOrders()
    {
        // Retrieve the access token and tenant ID from the database
        $token = XeroConnect::get()->first();
        $accessToken = $token->access_token;
        $tenant = XeroTenant::get()->first();
        $tenantId = $tenant->tenantId;
        $contact = Contact::get()->first();

        // Initialize a new Guzzle HTTP client
        $client = new Client();

        try {
            // Make a GET request to the Xero API to retrieve all purchase orders
            $response = $client->request('GET', uri: 'https://api.xero.com/api.xro/2.0/PurchaseOrders', options: [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Xero-tenant-id' => $tenantId,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            // Decode the response body into an associative array
            $purchaseOrders = json_decode($response->getBody()->getContents(), true)['PurchaseOrders'];

            // For each purchase order, create or update a PurchaseOrder record in the database
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

                // Store LineItems
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

            // Return a success message
            return response()->json(['message' => 'Purchase Orders synced successfully']);
        } catch (\Exception $e) {
            // If an exception occurs, return a JSON response with the error message
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        } catch (GuzzleException $e) {
            // If a GuzzleException occurs, return a JSON response with the error message
            return response()->json([
                'message' => 'Exception when calling Xero API: '.$e->getMessage(),
            ]);
        }
    }
}
