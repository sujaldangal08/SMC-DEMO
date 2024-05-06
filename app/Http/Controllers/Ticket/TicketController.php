<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketRequest;
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
                'status' => 'failure',
                'message' => 'Failed to fetch all tickets ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch a single ticket
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
                'status' => 'failure',
                'message' => 'Ticket not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to fetch tickets' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new ticket
     *
     * @param  Request  $request
     */
    public function store(TicketRequest $request): JsonResponse
    {
        try {

            $validatedData = $request->validated();
            // dd($validatedData);
            $tickets = [];
            $lotNumber = Str::random(10);
            $previousWeight = $validatedData['initial_truck_weight'] ?? 0;

            for ($index = 0; $index < count($validatedData['customer_id']); $index++) {
                [$ticketData, $truckWeight] = $this->prepareTicketData($validatedData, $previousWeight, $index, $lotNumber, null);
                $tickets[] = Ticket::create($ticketData);
                $previousWeight = $truckWeight;  // Update previous weight for next iteration
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket created successfully',
                'data' => $tickets,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to create ticket ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update ticket details
     *
     * @param  Request  $request
     */
    public function update(TicketRequest $request, string $ticketId): JsonResponse
    {
        try {
            $tickets = Ticket::where('ticket_number', $ticketId)->get();
            if ($tickets->isEmpty()) {
                throw new ModelNotFoundException();
            }
            $validatedData = $request->validate();

            $updatedTickets = [];

            $previousWeight = $validatedData['initial_truck_weight'] ?? 0;

            foreach ($tickets as $index => $ticket) {
                [$ticketData, $truckWeight] = $this->prepareTicketData($validatedData, $previousWeight, $index, $ticket->lot_number, $ticket->ticket_number);
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
                'status' => 'failure',
                'message' => 'Failed to update ticket ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a ticket
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
                'status' => 'failure',
                'message' => 'Ticket not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failure to delete ticket ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore a ticket
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
                'status' => 'failure',
                'message' => 'Ticket not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to restore ticket ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete a ticket
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
                'status' => 'failure',
                'message' => 'Ticket not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to permanently delete ticket ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Prepare ticket data
     *
     * @param  int  $lotNumber
     */
    private function prepareTicketData(array $validatedData, int $previousWeight, int $index, string $lotNumber, ?string $ticketNumber): array
    {
        $tareBin = $validatedData['tare_bin'][$index] ?? 0;
        $truckWeight = $validatedData['next_truck_weight'][$index] ?? 0;
        $fullBinWeight = $validatedData['full_bin_weight'][$index] ?? 0;
        $material = $validatedData['material'][$index] ?? null;
        $customerId = $validatedData['customer_id'][$index];
        if (request()->isMethod('post')) {
            $ticketNumber = 'TICKET-'.strtoupper(dechex($customerId)).'-'.Str::random(10);
        }
        $gross_weight = $validatedData['weighing_type'] === 'pallet'
            ? $fullBinWeight - $tareBin
            : $previousWeight - $truckWeight - $tareBin;

        $ticketData = [
            'rego_number' => $validatedData['rego_number'],
            'driver_id' => $validatedData['driver_id'],
            'route_id' => $validatedData['route_id'],
            'customer_id' => $customerId,
            'material' => $material,
            'weighing_type' => $validatedData['weighing_type'],
            'initial_truck_weight' => $previousWeight,
            'next_truck_weight' => $truckWeight,
            'full_bin_weight' => $fullBinWeight,
            'tare_bin' => $tareBin,
            'notes' => $validatedData['note'][$index],
            'ticket_type' => $validatedData['ticket_type'],
            'lot_number' => $lotNumber,
            'in_time' => $validatedData['in_time'],
            'out_time' => $validatedData['out_time'],
            'gross_weight' => $gross_weight,
            'ticket_number' => $ticketNumber,
            'in_time' => $validatedData['in_time'],
            'out_time' => $validatedData['out_time'],
        ];

        return [$ticketData, $truckWeight];
    }
}
