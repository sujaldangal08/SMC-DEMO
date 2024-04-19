<?php

namespace App\Http\Controllers\Xero;

use App\Http\Controllers\Controller;
use App\Models\Xero\Contact;
use App\Models\Xero\Address;
use App\Models\Xero\Phone;
use App\Models\Xero\Balances;
use App\Models\Xero\PurchaseOrder;
use App\Models\Xero\LineItem;
use Illuminate\Http\Request;

class XeroController extends Controller
{
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
            'Contacts' => $transformedContacts
        ], 200);
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
                'Date' => '/Date(' . (new \DateTime($purchaseOrder->date))->getTimestamp() . '000+0000)/',
                'DeliveryDate' => '/Date(' . (new \DateTime($purchaseOrder->delivery_date))->getTimestamp() . '000+0000)/',
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
                    'UpdatedDateUTC' => '/Date(' . (new \DateTime($purchaseOrder->contact->updated_at))->getTimestamp() . '000+0000)/',
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
            'Contacts' => $transformedPurchaseOrder
        ], 200);
    }

}
