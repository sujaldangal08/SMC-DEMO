<?php

namespace App\Http\Controllers\Xero;

use App\Http\Controllers\Controller;
use App\Models\Xero\Contact;
use App\Models\Xero\XeroConnect;
use App\Models\Xero\XeroSetting;
use App\Models\Xero\XeroTenant;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class XeroController extends Controller
{
    public function xeroConnect(): \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => env('XERO_CLIENT_ID'),
            'redirect_uri' => env('XERO_REDIRECT_URI'),
            'scope' => 'email profile openid accounting.settings accounting.transactions accounting.contacts offline_access', // Adjust scope as needed
        ]);

        return redirect('https://login.xero.com/identity/connect/authorize?'.$query);
    }

    /**
     * @throws GuzzleException
     */
    public function xeroCallback(Request $request)
    {
        $code = $request->query('code');

        $client = new Client();
        $response = $client->post('https://identity.xero.com/connect/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => env('XERO_CLIENT_ID'),
                'client_secret' => env('XERO_CLIENT_SECRET'),
                'redirect_uri' => env('XERO_REDIRECT_URI'),
                'code' => $code,
            ],
        ]);

        $responseBody = json_decode((string) $response->getBody(), true);

        $accessToken = $responseBody['access_token'];
        $refreshToken = $responseBody['refresh_token'];

        $xeroConnect = XeroConnect::first();

        // Save the data to the XeroConnect model
        $xeroConnect->update([
            'id_token' => $responseBody['id_token'],
            'access_token' => $accessToken,
            'expires_in' => $responseBody['expires_in'],
            'token_type' => $responseBody['token_type'],
            'refresh_token' => $refreshToken,
            'scope' => $responseBody['scope'],
        ]);

        return response()->json([
            'message' => 'Successfully connected to Xero',
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ], 200);
    }

    public function xeroRefresh(): \Illuminate\Http\JsonResponse
    {
        $xeroConnect = XeroConnect::first();

        $client = new Client();
        $response = $client->post('https://identity.xero.com/connect/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => env('XERO_CLIENT_ID'),
                'client_secret' => env('XERO_CLIENT_SECRET'),
                'refresh_token' => $xeroConnect->refresh_token,
            ],
        ]);

        $responseBody = json_decode((string) $response->getBody(), true);
        $xeroConnect->update([
            'access_token' => $responseBody['access_token'],
            'expires_in' => $responseBody['expires_in'],
            'token_type' => $responseBody['token_type'],
            'refresh_token' => $responseBody['refresh_token'],
            'scope' => $responseBody['scope'],
        ]);

        return response()->json([
            'message' => 'Successfully refreshed the access token',
            'access_token' => $responseBody['access_token'],
            'refresh_token' => $responseBody['refresh_token'],
        ], 200);
    }

    public function xeroTenant()
    {
        $xeroConnect = XeroConnect::first();

        $client = new Client();
        $response = $client->get('https://api.xero.com/connections', [
            'headers' => [
                'Authorization' => 'Bearer '.$xeroConnect->access_token,
            ],
        ]);

        $responseBody = json_decode((string) $response->getBody(), true);

        // Save the data to the XeroTenant model
        $xeroTenant = XeroTenant::first();
        foreach ($responseBody as $tenant) {
            $xeroTenant->update([
                'connection_id' => $tenant['id'],
                'authEventId' => $tenant['authEventId'],
                'tenantId' => $tenant['tenantId'],
                'tenantType' => $tenant['tenantType'],
                'tenantName' => $tenant['tenantName'],
                'xero_connect_id' => $xeroConnect->id,
                'createdDateUtc' => $tenant['createdDateUtc'],
                'updatedDateUtc' => $tenant['updatedDateUtc'],
            ]);
        }

        return response()->json([
            'message' => 'Successfully fetched and saved the tenants',
            'tenants' => $responseBody,
        ], 200);
    }

    public function getXeroData(): \Illuminate\Http\JsonResponse
    {
        // Fetch all data from the database
        $contacts = Contact::with(['addresses', 'phones', 'balances'])->get();

        // Transform the data to match the provided JSON format
        $transformedContacts = $contacts->map(function ($contact) {
            return [
                'ContactID' => $contact->contact_id,
                'ContactStatus' => $contact->contact_status,
                'Name' => $contact->name,
                'Addresses' => $contact->addresses->map(function ($address) {
                    return ['AddressType' => $address->address_type];
                }),
                'Phones' => $contact->phones->map(function ($phone) {
                    return ['PhoneType' => $phone->address_type];
                }),
                'UpdatedDateUTC' => $contact->updated_at->timestamp,
                'ContactGroups' => [],
                'IsSupplier' => $contact->is_supplier,
                'IsCustomer' => $contact->is_customer,
                'Balances' => [
                    'AccountsReceivable' => [
                        'Outstanding' => optional($contact->balances)->accounts_receivable_outstanding,
                        'Overdue' => optional($contact->balances)->accounts_receivable_overdue,
                    ],
                    'AccountsPayable' => [
                        'Outstanding' => optional($contact->balances)->accounts_payable_outstanding,
                        'Overdue' => optional($contact->balances)->accounts_payable_overdue,
                    ],
                ],
                'ContactPersons' => [],
                'HasAttachments' => $contact->has_attachments,
                'HasValidationErrors' => $contact->has_validation_errors,
            ];
        });

        // Return a JSON response with the status, message, total number of data, and the data
        return response()->json([
            'Id' => '58b5344c-edf0-44ce-9e54-f5540b525888',
            'Status' => 'OK',
            'ProviderName' => 'LaravelApp',
            'DateTimeUTC' => now()->timestamp,
            'Contacts' => $transformedContacts,
        ], 200);
    }

    public function getXeroCredentials(): JsonResponse
    {
        $xeroSetting = XeroSetting::first();

        if (! $xeroSetting) {
            return response()->json(['message' => 'XeroSetting not found'], 404);
        }

        return response()->json([
            'xero_client_id' => $xeroSetting->xero_client_id,
            'xero_client_secret' => $xeroSetting->xero_client_secret,
        ], 200);
    }

    public function storeXeroCredentials(Request $request): JsonResponse
    {
        // Validate the request...
        $request->validate([
            'xero_client_id' => 'required',
            'xero_client_secret' => 'required',
        ]);

        // Get the first XeroSetting record or create a new one if it doesn't exist
        $xeroSetting = XeroSetting::first() ?? new XeroSetting();

        // Update the encrypted XeroSetting record with the provided credentials
        $xeroSetting->xero_client_id = encrypt($request->xero_client_id);
        $xeroSetting->xero_client_secret = encrypt($request->xero_client_secret);
        $xeroSetting->save();

        return response()->json(['message' => 'Xero credentials stored successfully'], 200);
    }

    public function updateXeroCredentials(Request $request, $id): JsonResponse
    {
        // Validate the request...
        $request->validate([
            'xero_client_id' => 'required',
            'xero_client_secret' => 'required',
        ]);

        // Get the XeroSetting record with the given ID
        $xeroSetting = XeroSetting::find($id);

        if (! $xeroSetting) {
            return response()->json(['message' => 'XeroSetting not found'], 404);
        }

        // Update the XeroSetting record with the provided credentials
        $xeroSetting->xero_client_id = encrypt($request->xero_client_id);
        $xeroSetting->xero_client_secret = encrypt($request->xero_client_secret);
        $xeroSetting->save();

        return response()->json(['message' => 'Xero credentials updated successfully'], 200);
    }

    public function getPurchaseOrder(): \Illuminate\Http\JsonResponse
    {

        $contact = Contact::with(['addresses', 'phones', 'balances', 'purchaseOrder'])->first();
        $purchaseOrder = $contact->purchaseOrder;

        $transformedPurchaseOrder = $purchaseOrder->map(function ($purchaseOrder) {
            return [
                'PurchaseOrderID' => $purchaseOrder->purchase_order_id,
                'PurchaseOrderNumber' => $purchaseOrder->purchase_order_number,
                'DateString' => $purchaseOrder->date_string,
                'Date' => '/Date('.(new \DateTime($purchaseOrder->date))->getTimestamp().'000+0000)/',
                'DeliveryDate' => '/Date('.(new \DateTime($purchaseOrder->delivery_date))->getTimestamp().'000+0000)/',
                'DeliveryAddress' => $purchaseOrder->delivery_address,
                'AttentionTo' => $purchaseOrder->attention_to,
                'Telephone' => $purchaseOrder->telephone,
                'DeliveryInstructions' => $purchaseOrder->delivery_instructions,
                'HasErrors' => $purchaseOrder->has_errors,
                'IsDiscounted' => $purchaseOrder->is_discounted,
                'Reference' => $purchaseOrder->reference,
                'Type' => $purchaseOrder->type,
                'CurrencyRate' => $purchaseOrder->currency_rate,
                'CurrencyCode' => $purchaseOrder->currency_code,
                'Contact' => [
                    'ContactID' => $purchaseOrder->contact->id,
                    'ContactStatus' => $purchaseOrder->contact->status,
                    'Name' => $purchaseOrder->contact->name,
                    'Addresses' => $purchaseOrder->contact->addresses,
                    'Phones' => $purchaseOrder->contact->phones,
                    'UpdatedDateUTC' => '/Date('.(new \DateTime($purchaseOrder->contact->updated_at))->getTimestamp().'000+0000)/',
                    'ContactGroups' => $purchaseOrder->contact->contactGroups,
                    'DefaultCurrency' => $purchaseOrder->contact->defaultCurrency,
                    'ContactPersons' => $purchaseOrder->contact->contactPersons,
                    'HasValidationErrors' => $purchaseOrder->contact->hasValidationErrors,
                ],
                'LineItems' => $purchaseOrder->line_items->map(function ($lineItem) {
                    return [
                        'ItemCode' => $lineItem->item_code,
                        'Description' => $lineItem->description,
                        'UnitAmount' => $lineItem->unit_amount,
                        'TaxType' => $lineItem->tax_type,
                        'TaxAmount' => $lineItem->tax_amount,
                        'LineAmount' => $lineItem->line_amount,
                        'AccountCode' => $lineItem->account_code,
                        'Tracking' => $lineItem->tracking,
                        'Quantity' => $lineItem->quantity,
                        'LineItemID' => $lineItem->line_item_id,
                    ];
                }),
            ];
        });

        return response()->json([
            'Id' => '58b5344c-edf0-44ce-9e54-f5540b525888',
            'Status' => 'OK',
            'ProviderName' => 'LaravelApp',
            'DateTimeUTC' => now()->timestamp,
            'Contacts' => $transformedPurchaseOrder,
        ], 200);
    }
}
