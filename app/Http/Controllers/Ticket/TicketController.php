<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\ValidatesRoles;
use Illuminate\Support\Str;


class TicketController extends Controller
{
    use ValidatesRoles;

    public function index(): JsonResponse
    {
        $ticket = Ticket::all();
        return response()->json([
            'status' => 'success',
            'message' => 'All tickets fetched successfully',
            'total' => $ticket->count(),
            'data' => $ticket
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $ticket = Ticket::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Ticket fetched successfully',
                'data' => $ticket
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    //Manual Entry of Ticket
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'rego_number' => 'required|string',
                'driver_id' => ['required', $this->roleRule('driver')],
                'customer_id' => ['required', $this->roleRule('customer'), 'array'],
                'route_id' => 'nullable|exists:routes,id',  //For automation of ticket generation for schedule
                'material' => ['required', 'array', 'size:' . count($request->input('customer_id'))],
                'weighing_type' => 'required|in:bridge,pallet',
                'initial_truck_weight' => $request->input('weighing_type') === 'pallet' ? 'nullable' : ['required', 'array', 'size:' . count($request->input('customer_id'))],
                'next_truck_weight' => $request->input('weighing_type') === 'pallet' ? 'nullable' : ['required', 'array', 'size:' . count($request->input('customer_id'))],
                'tare_bin' => ['required', 'array', 'size:' . count($request->input('customer_id'))],
                'full_bin_weight' => $request->input('weighing_type') === 'pallet' ? ['required', 'array', 'size:' . count($request->input('customer_id'))] : 'nullable',
                'ticked_type' => 'in:direct,schedule',
                'in_time' => 'required|date',
                'out_time' => 'required|date',
            ]);

            $tickets = [];

            $lotNumber = Str::random(10);
            $previousWeight = $request->input('initial_truck_weight');
            $customerIds = $request->input('customer_id');
            $truckWeights = $request->input('next_truck_weight');
            $tareBins = $request->input('tare_bin');
            $materials = $request->input('material');
            $weighingType = $request->input('weighing_type');
            $fullBinWeights = $request->input('full_bin_weight');

            for ($index = 0; $index < count($customerIds); $index++) {
                $tareBin = isset($tareBins[$index]) ? $tareBins[$index] : 0;
                $truckWeight = 0;
                $fullBinWeight = 0;
                if ($weighingType === 'pallet') {
                    $fullBinWeight = isset($fullBinWeights[$index]) ? $fullBinWeights[$index] : 0;
                    $gross_weight = $fullBinWeight - $tareBin;
                } else {
                    $truckWeight = isset($truckWeights[$index]) ? $truckWeights[$index] : 0;
                    $gross_weight = $previousWeight - $truckWeight - $tareBin;
                }
                $material = isset($materials[$index]) ? $materials[$index] : null;
                $customerId =  $customerIds[$index];
                $ticketNumber = 'TICKET-' . strtoupper(dechex($customerId)) . '-' . date('YmdHis');


                $ticketData = [
                    'rego_number' => $request->input('rego_number'),
                    'driver_id' => $request->input('driver_id'),
                    'route_id' => $request->input('route_id'),
                    'customer_id' => $customerIds[$index],
                    'material' => $material,
                    'weighing_type' => $request->input('weighing_type'),
                    'next_truck_weight' => $truckWeight,
                    'full_bin_weight' => $fullBinWeight,
                    'tare_bin' => $tareBin,
                    'ticket_type' => $request->input('ticket_type'),
                    'lot_number' => $lotNumber,
                    'in_time' => $request->input('in_time'),
                    'out_time' => $request->input('out_time'),
                    'gross_weight' => $gross_weight,
                    'ticket_number' => $ticketNumber,
                ];
                $tickets[] = Ticket::create($ticketData);

                $previousWeight = $truckWeights;  // Update previous weight for next iteration
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket created successfully',
                'data' => $tickets
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
