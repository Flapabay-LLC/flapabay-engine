<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Submit a new support ticket
     */
    public function submitSupportTicket(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'priority' => 'required|in:low,medium,high',
                'category' => 'required|in:technical,billing,account,booking,other',
                'attachments' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ticket = SupportTicket::create([
                'user_id' => auth()->id(),
                'ticket_number' => 'TICK-' . strtoupper(Str::random(8)),
                'subject' => $request->subject,
                'description' => $request->description,
                'priority' => $request->priority,
                'category' => $request->category,
                'attachments' => $request->attachments,
                'status' => 'open'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Support ticket created successfully',
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create support ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View support tickets
     */
    public function viewSupportTickets(Request $request)
    {
        try {
            $query = SupportTicket::with(['user', 'responses'])
                ->where('user_id', auth()->id());

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('category')) {
                $query->where('category', $request->category);
            }
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $tickets = $query->paginate($request->get('per_page', 10));

            return response()->json([
                'status' => 'success',
                'data' => $tickets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch support tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ticket details
     */
    public function getTicketDetails($ticketId)
    {
        try {
            $ticket = SupportTicket::with(['user', 'responses.user'])
                ->where('user_id', auth()->id())
                ->findOrFail($ticketId);

            return response()->json([
                'status' => 'success',
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch ticket details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add response to ticket
     */
    public function addTicketResponse(Request $request, $ticketId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string',
                'attachments' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ticket = SupportTicket::where('user_id', auth()->id())
                ->findOrFail($ticketId);

            $response = SupportTicketResponse::create([
                'ticket_id' => $ticketId,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'attachments' => $request->attachments,
                'is_staff_response' => false
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Response added successfully',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch FAQs
     */
    public function fetchFaqs(Request $request)
    {
        try {
            $query = Faq::active()->ordered();

            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            $faqs = $query->get();

            return response()->json([
                'status' => 'success',
                'data' => $faqs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch FAQs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 