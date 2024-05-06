<?php

namespace App\Http\Controllers\Xero;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Xero\Contact;
use App\Models\Xero\XeroConnect;
use App\Models\Xero\XeroTenant;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Crypt;

class XeroController extends Controller
{
    /**
     * This method is responsible for initiating the connection to Xero.
     * It first retrieves the Xero credentials.
     * Then, it builds a query string with the necessary parameters for the Xero authorization URL.
     * Finally, it redirects the user to the Xero authorization URL.
     *
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     */
    public function xeroConnect()
    {
       try{
         // Retrieve the Xero credentials
         $credentials = $this->getXero();

         // Build a query string with the necessary parameters for the Xero authorization URL
         $query = http_build_query([
             'response_type' => 'code',
             'client_id' => $credentials['xero_client_id'],
             'redirect_uri' => config('services.xero.redirect_uri'),
             'scope' => 'email profile openid accounting.settings accounting.transactions accounting.contacts offline_access', // Adjust scope as needed
         ]);

         // Redirect the user to the Xero authorization URL
         return redirect('https://login.xero.com/identity/connect/authorize?'.$query);
       } catch (\Exception $e) {
         return response()->json([
             'status' => 'failure',
             'message' => 'An error occurred while connecting to Xero: '. $e->getMessage(),
         ], 500);
       } catch (GuzzleException $e) {
         return response()->json([
             'status' => 'failure',
             'message' => 'An error occurred while connecting to Xero: '. $e->getMessage(),
         ], 500);
       }
    }

    /**
     * This method is responsible for retrieving the Xero credentials.
     * It first retrieves all settings from the database.
     * Then, it decrypts the client_id and client_secret.
     * If the settings are not found, it returns a JSON response with an error message.
     *
     * @return array|JsonResponse
     */
    protected function getXero()
    {
        try {
            // Retrieve all settings from the database
            $xeroSetting = Setting::all();

            // If the settings are not found, return a JSON response with an error message
            if ($xeroSetting->isEmpty()) {
                return response()->json(['message' => 'XeroSetting not found'], 404);
            }

            $client_id = $xeroSetting['0']['setting_value'];
            $client_secret = $xeroSetting['1']['setting_value'];

            // Decrypt the client_id and client_secret
            return [
                'xero_client_id' => Crypt::decryptString($client_id),
                'xero_client_secret' => Crypt::decryptString($client_secret),
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while retrieving Xero settings'], 500);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while retrieving Xero settings: '. $e->getMessage(),
            ], 500);
        }
    }

    /**
     * This method is responsible for handling the callback from Xero after the user has authorized the application.
     * It first retrieves the authorization code from the request query parameters.
     * Then, it retrieves the Xero credentials.
     * It makes a POST request to the Xero API to exchange the authorization code for an access token.
     * It updates the XeroConnect record in the database with the new access token and other related information.
     * If an exception occurs during the process, it returns a JSON response with the error message.
     *
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function xeroCallback(Request $request)
    {
        try{
                    // Retrieve the authorization code from the request query parameters
        $code = $request->query('code');

        // Retrieve the Xero credentials
        $credentials = $this->getXero();

        // Initialize a new Guzzle HTTP client
        $client = new Client();

        // Make a POST request to the Xero API to exchange the authorization code for an access token
        $response = $client->post('https://identity.xero.com/connect/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $credentials['xero_client_id'],
                'client_secret' => $credentials['xero_client_secret'],
                'redirect_uri' => config('services.xero.redirect_uri'),
                'code' => $code,
            ],
        ]);

        // Decode the response body into an associative array
        $responseBody = json_decode((string) $response->getBody(), true);

        // Get the access token and refresh token from the response
        $accessToken = $responseBody['access_token'];
        $refreshToken = $responseBody['refresh_token'];

        // Get the first XeroConnect record or create a new one if it doesn't exist
        $xeroConnect = XeroConnect::first() ?? new XeroConnect();

        // Save the data to the XeroConnect model
        $xeroConnect->update([
            'id_token' => $responseBody['id_token'],
            'access_token' => $accessToken,
            'expires_in' => $responseBody['expires_in'],
            'token_type' => $responseBody['token_type'],
            'refresh_token' => $refreshToken,
            'scope' => $responseBody['scope'],
        ]);

        // Return a JSON response with the status, message, and the data
        return response()->json([
            'message' => 'Successfully connected to Xero',
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while connecting to Xero'
            ], 500);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while connecting to Xero: '. $e->getMessage(),
            ], 500);
        }
    }

    /**
     * This method is responsible for refreshing the Xero access token.
     * It first retrieves the first XeroConnect record from the database.
     * Then, it makes a POST request to the Xero API to refresh the access token.
     * It updates the XeroConnect record in the database with the new access token and other related information.
     * If an exception occurs during the process, it returns a JSON response with the error message.
     */
    public function xeroRefresh(): JsonResponse
    {
        try{
                    // Fetch the first XeroConnect record from the database
        $xeroConnect = XeroConnect::first();

        // Retrieve the Xero credentials
        $credentials = $this->getXero();

        // Initialize a new Guzzle HTTP client
        $client = new Client();

        // Make a POST request to the Xero API to refresh the access token
        $response = $client->post('https://identity.xero.com/connect/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $credentials['xero_client_id'],
                'client_secret' => $credentials['xero_client_secret'],
                'refresh_token' => $xeroConnect->refresh_token,
            ],
        ]);

        // Decode the response body into an associative array
        $responseBody = json_decode((string) $response->getBody(), true);

        // Update the XeroConnect record in the database with the new access token and other related information
        $xeroConnect->update([
            'access_token' => $responseBody['access_token'],
            'expires_in' => $responseBody['expires_in'],
            'token_type' => $responseBody['token_type'],
            'refresh_token' => $responseBody['refresh_token'],
            'scope' => $responseBody['scope'],
        ]);

        // Return a JSON response with the status, message, and the data
        return response()->json([
            'message' => 'Successfully refreshed the access token',
            'access_token' => $responseBody['access_token'],
            'refresh_token' => $responseBody['refresh_token'],
        ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while refreshing the access token'
            ], 500);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while refreshing the access token: '. $e->getMessage(),
            ], 500);
        }
    }

    /**
     * This method is responsible for fetching and saving the tenants from Xero.
     * It first retrieves the first XeroConnect record from the database.
     * Then, it makes a GET request to the Xero API to retrieve all tenants.
     * For each tenant, it updates the XeroTenant record in the database.
     * If an exception occurs during the process, it returns a JSON response with the error message.
     *
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function xeroTenant()
    {
        try{
             // Fetch the first XeroConnect record from the database
        $xeroConnect = XeroConnect::first();

        // Initialize a new Guzzle HTTP client
        $client = new Client();

        // Make a GET request to the Xero API to retrieve all tenants
        $response = $client->get('https://api.xero.com/connections', [
            'headers' => [
                'Authorization' => 'Bearer '.$xeroConnect->access_token,
            ],
        ]);

        // Decode the response body into an associative array
        $responseBody = json_decode((string) $response->getBody(), true);

        // Fetch the first XeroTenant record from the database
        $xeroTenant = XeroTenant::first();

        // For each tenant, update the XeroTenant record in the database
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

        // Return a JSON response with the status, message, and the data
        return response()->json([
            'message' => 'Successfully fetched and saved the tenants',
            'tenants' => $responseBody,
        ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while fetching and saving the tenants'
            ], 500);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while fetching and saving the tenants: '. $e->getMessage(),
            ], 500);
        }
    }

    /**
     * This method is responsible for fetching all contacts from the database and transforming the data to match a specific format.
     * It first retrieves all contacts from the database along with their related addresses, phones, and balances.
     * Then, it transforms the contact data to match a specific format.
     * Finally, it returns a JSON response with the status, message, total number of data, and the data.
     */
    public function getXeroData(): JsonResponse
    {
        try{
            // Fetch all contacts from the database along with their related addresses, phones, and balances
        $contacts = Contact::with(['addresses', 'phones', 'balances'])->get();

        // Transform the contact data to match a specific format
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while fetching Xero data'
            ], 500);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'An error occurred while fetching Xero data: '. $e->getMessage(),
            ], 500);
        }
    }

    /**
     * This method is responsible for fetching a purchase order.
     * It first retrieves the first contact from the database along with its related addresses, phones, balances, and purchase orders.
     * Then, it transforms the purchase order data to match a specific format.
     * If an exception occurs during the process, it returns a JSON response with the error message.
     */
    public function getPurchaseOrder(): JsonResponse
    {
       try{
         // Fetch the first contact from the database along with its related addresses, phones, balances, and purchase orders
         $contact = Contact::with(['addresses', 'phones', 'balances', 'purchaseOrder'])->first();
         $purchaseOrder = $contact->purchaseOrder;

         // Transform the purchase order data to match a specific format
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

         // Return a JSON response with the status, message, total number of data, and the data
         return response()->json([
             'Id' => '58b5344c-edf0-44ce-9e54-f5540b525888',
             'Status' => 'OK',
             'ProviderName' => 'LaravelApp',
             'DateTimeUTC' => now()->timestamp,
             'Contacts' => $transformedPurchaseOrder,
         ], 200);
        } catch (\Exception $e) {
         return response()->json([
             'status' => 'failure',
             'message' => 'An error occurred while fetching the purchase order'
         ], 500);
        }catch (GuzzleException $e) {
         return response()->json([
             'status' => 'failure',
             'message' => 'An error occurred while fetching the purchase order: '. $e->getMessage(),
         ], 500);
       }
    }
}
