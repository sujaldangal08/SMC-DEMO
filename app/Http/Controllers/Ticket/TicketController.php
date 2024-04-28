<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Traits\ValidatesRoles;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    use ValidatesRoles;

    /**
     * Fetch all tickets
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $ticket = Ticket::all();

            return response()->json([
                'status' => 'success',
                'message' => 'All tickets fetched successfully',
                'total' => $ticket->count(),
                'data' => $ticket,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch a single ticket
     *
     * @param string $ticketNumber
     * @return JsonResponse
     */
    public function show(string $ticketNumber): JsonResponse
    {
        try {
            $ticket = Ticket::where('ticket_number', $ticketNumber)->get();
            $ticket->load('wastes');

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket fetched successfully',
                'data' => $ticket,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new ticket
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'rego_number' => 'required|string',
                'driver_id' => ['required', $this->roleRule('driver')],
                'customer_id' => ['required', $this->roleRule('customer'), 'array'],
                'route_id' => 'nullable|exists:routes,id',  //For automation of ticket generation for schedule
                'material' => ['required', 'array', 'size:'.count($request->input('customer_id'))],
                'weighing_type' => 'required|in:bridge,pallet',
                'initial_truck_weight' => $request->input('weighing_type') === 'pallet' ? 'nullable' : ['required', 'array', 'size:'.count($request->input('customer_id'))],
                'next_truck_weight' => $request->input('weighing_type') === 'pallet' ? 'nullable' : ['required', 'array', 'size:'.count($request->input('customer_id'))],
                'tare_bin' => ['required', 'array', 'size:'.count($request->input('customer_id'))],
                'full_bin_weight' => $request->input('weighing_type') === 'pallet' ? ['required', 'array', 'size:'.count($request->input('customer_id'))] : 'nullable',
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
                $customerId = $customerIds[$index];
                $ticketNumber = 'TICKET-'.strtoupper(dechex($customerId)).'-'.Str::random(10);

                $ticketData = [
                    'rego_number' => $request->input('rego_number'),
                    'driver_id' => $request->input('driver_id'),
                    'route_id' => $request->input('route_id'),
                    'customer_id' => $customerIds[$index],
                    'material' => $material,
                    'weighing_type' => $request->input('weighing_type'),
                    'initial_truck_weight' => $previousWeight,
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
                'data' => $tickets,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update ticket details
     *
     * @param Request $request
     * @param string $ticketId
     * @return JsonResponse
     */

    public function update(Request $request, string $ticketId): JsonResponse
    {
        try {
            $request->validate([
                'rego_number' => 'required|string',
                'driver_id' => ['required', $this->roleRule('driver')],
                'customer_id' => ['required', $this->roleRule('customer'), 'array'],
                'route_id' => 'nullable|exists:routes,id',
                'material' => ['required', 'array', 'size:'.count($request->input('customer_id'))],
                'weighing_type' => 'required|in:bridge,pallet',
                'initial_truck_weight' => '',
                'next_truck_weight' => $request->input('weighing_type') === 'pallet' ? 'nullable' : ['required', 'array', 'size:' . count($request->input('customer_id'))],
                'tare_bin' => ['required', 'array', 'size:' . count($request->input('customer_id'))],
                'full_bin_weight' => $request->input('weighing_type') === 'pallet' ? ['required', 'array', 'size:' . count($request->input('customer_id'))] : 'nullable',
                'ticked_type' => 'in:direct,schedule',
                'note' => 'nullable|array|size:'.count($request->input('customer_id')),
                'in_time' => 'nullable|date',
                'out_time' => 'nullable|date',
            ]);

            $tickets = Ticket::where('ticket_number', $ticketId)->get();
            if ($tickets->isEmpty()) {
                throw new ModelNotFoundException();
            }

            $updatedTickets = [];

            $previousWeight = $request->input('initial_truck_weight'); // Set initial_truck_weight to 1
            $customerIds = $request->input('customer_id');
            $truckWeights = $request->input('next_truck_weight');
            $tareBins = $request->input('tare_bin');
            $materials = $request->input('material');
            $weighingType = $request->input('weighing_type');
            $fullBinWeights = $request->input('full_bin_weight');

            foreach ($tickets as $index => $ticket) {
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

                $ticketData = [
                    'rego_number' => $request->input('rego_number'),
                    'driver_id' => $request->input('driver_id'),
                    'route_id' => $request->input('route_id'),
                    'customer_id' => $customerIds[$index],
                    'material' => $material,
                    'initial_truck_weight' => $previousWeight, //comes from the machine
                    'weighing_type' => $request->input('weighing_type'),
                    'next_truck_weight' => $truckWeight,
                    'full_bin_weight' => $fullBinWeight,
                    'tare_bin' => $tareBin,
                    'ticket_type' => $request->input('ticket_type'),
                    'gross_weight' => $gross_weight,
                ];
                $previousWeight = $truckWeight;  // Update previous weight for next iteration
                $ticket->update($ticketData);
                $updatedTickets[] = $ticket;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket updated successfully',
                'data' => $updatedTickets,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a ticket
     *
     * @param string $ticketId
     * @return JsonResponse
     */
    public function delete(string $ticketId): JsonResponse
    {
        try {
            $ticket = Ticket::where('ticket_number', $ticketId)->first();
            $ticket->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore a ticket
     *
     * @param string $ticketId
     * @return JsonResponse
     */
    public function restore(string $ticketId): JsonResponse
    {
        try {
            $ticket = Ticket::withTrashed()->where('ticket_number', $ticketId)->first();
            $ticket->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket restored successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete a ticket
     *
     * @param string $ticketId
     * @return JsonResponse
     */
    public function permanentDelete(string $ticketId): JsonResponse
    {
        try {
            $ticket = Ticket::withTrashed()->where('ticket_number', $ticketId)->first();
            $ticket->forceDelete();

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket permanently deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
