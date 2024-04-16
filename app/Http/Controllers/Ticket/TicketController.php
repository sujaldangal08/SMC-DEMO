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

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'rego_number' => 'required|string',
                'driver_id' => ['required', $this->roleRule('driver')],
                'customer_id' => ['required', $this->roleRule('customer'), 'array'],
                'route_id' => 'nullable|exists:routes,id',  //For automation of ticket generation for schedule
                'material' => 'required|array',
                'weighing_type' => 'required|in:bridge,pallet',
                'initial_truck_weight' => 'required',
                'next_truck_weight' => $request->input('weighing_type') === 'pallet' ? 'nullable' : 'required|array',
                'tare_bin' => 'required|array',
                'full_bin_weight' => $request->input('weighing_type') === 'pallet' ? 'required|array' : 'nullable',
                'ticked_type' => 'in:direct,schedule',
                'in_time' => 'required|date',
                'out_time' => 'required|date',
            ]);

            $tickets = [];

            $lotNumber = Str::random(10);

            // $truckWeight = $request->input('next_truck_weight');
            // $previousWeight = is_array($truckWeight) && isset($truckWeight[0]) ? $truckWeight[0] : 0;

            // foreach ($request->input('customer_id') as $index => $customerId) {
            //     $currentWeight = isset($request->input('next_truck_weight')[$index]) ? $request->input('next_truck_weight')[$index] : 0;
            //     $tareBin = $request->input('tare_bin')[$index];

            //     $gross_weight = $previousWeight - $currentWeight - $tareBin;

            //     $ticketData = [
            //         'rego_number' => $request->input('rego_number'),
            //         'driver_id' => $request->input('driver_id'),
            //         'customer_id' => $customerId,
            //         'material' => $request->input('material')[$index],
            //         'weighing_type' => $request->input('weighing_type'),
            //         'next_truck_weight' => $currentWeight,
            //         'tare_bin' => $tareBin,
            //         'ticket_type' => $request->input('ticket_type'),
            //         'lot_number' => $request->input('lot_number')[$index],
            //         'in_time' => $request->input('in_time'),
            //         'out_time' => $request->input('out_time'),
            //         'lot_number' => $lotNumber,
            //         'gross_weight' => $gross_weight,
            //     ];

            //     $tickets[] = Ticket::create($ticketData);

            //     $previousWeight = $currentWeight;  // Update previous weight for next iteration
            // }

            $truckWeight = $request->input('next_truck_weight');
            // $previousWeight = is_array($truckWeight) && isset($truckWeight[0]) ? $truckWeight[0] : 0;
            $previousWeight = $request->input('initial_truck_weight');

            $customerIds = $request->input('customer_id');
            $truckWeights = $request->input('next_truck_weight');
            $tareBins = $request->input('tare_bin');
            $materials = $request->input('material');

            for ($index = 0; $index < count($customerIds); $index++) {
                $currentWeight = isset($truckWeights[$index]) ? $truckWeights[$index] : 0;
                $tareBin = isset($tareBins[$index]) ? $tareBins[$index] : 0;
                $material = isset($materials[$index]) ? $materials[$index] : null;

                $gross_weight = $previousWeight - $currentWeight - $tareBin;
                // dd($gross_weight, $previousWeight, $currentWeight, $tareBin);

                $ticketData = [
                    'rego_number' => $request->input('rego_number'),
                    'driver_id' => $request->input('driver_id'),
                    'route_id' => $request->input('route_id'),
                    'customer_id' => $customerIds[$index],
                    'material' => $material,
                    'weighing_type' => $request->input('weighing_type'),
                    'next_truck_weight' => $currentWeight,
                    'tare_bin' => $tareBin,
                    'ticket_type' => $request->input('ticket_type'),
                    'lot_number' => $lotNumber,
                    'in_time' => $request->input('in_time'),
                    'out_time' => $request->input('out_time'),
                    'gross_weight' => $gross_weight,
                    'ticket_number' => 'TICKET-' . Str::random(10),
                ];
                $tickets[] = Ticket::create($ticketData);

                $previousWeight = $currentWeight;  // Update previous weight for next iteration
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
}
