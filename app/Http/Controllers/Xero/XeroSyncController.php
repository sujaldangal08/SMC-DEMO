<?php

namespace App\Http\Controllers\Xero;

use App\Http\Controllers\Controller;
use App\Models\Xero\Address;
use App\Models\Xero\Contact;
use App\Models\Xero\Phone;
use App\Models\Xero\XeroConnect;
use App\Models\Xero\XeroTenant;
use GuzzleHttp\Client;

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
//                        dd($contacts);
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
//
            }
            return response()->json(['message' => 'Contacts synced successfully']);
        } catch (\Exception $e) {
            echo 'Exception when calling Xero API: ', $e->getMessage(), PHP_EOL;
        }
    }
}
