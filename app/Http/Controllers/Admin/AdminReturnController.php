<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminReturnController extends Controller
{
    use \App\Http\Traits\HasPaginationLimit;

    /**
     * Display list of all return requests
     */
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number or user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            })->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $this->getPerPage($request, 20);
        $returnRequests = $query->paginate($perPage)->appends($request->query());

        // Count pending returns for sidebar badge
        $pendingCount = ReturnRequest::where('status', ReturnRequest::STATUS_PENDING)->count();

        $breadcrumbs = [
            ['title' => 'Management'],
            ['title' => 'Return Requests'],
        ];

        return view('admin.returns.index', compact('returnRequests', 'pendingCount', 'perPage', 'breadcrumbs'));
    }

    /**
     * Show return request details
     */
    public function show(ReturnRequest $returnRequest)
    {
        $returnRequest->load(['order.orderItems', 'user']);

        return view('admin.returns.show', compact('returnRequest'));
    }

    /**
     * Approve return request
     */
    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        if (!$returnRequest->isPending()) {
            return back()->with('error', 'Only pending return requests can be approved.');
        }

        $validated = $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $returnRequest->approve($validated['admin_response']);

        return back()->with('success', 'Return request approved successfully. Customer has been notified.');
    }

    /**
     * Reject return request
     */
    public function reject(Request $request, ReturnRequest $returnRequest)
    {
        if (!$returnRequest->isPending()) {
            return back()->with('error', 'Only pending return requests can be rejected.');
        }

        $validated = $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $returnRequest->reject($validated['admin_response']);

        return back()->with('success', 'Return request rejected. Customer has been notified.');
    }

    /**
     * Confirm return received and process refund
     */
    public function confirmReceived(ReturnRequest $returnRequest)
    {
        if (!$returnRequest->isApproved()) {
            return back()->with('error', 'Return must be approved before confirming receipt.');
        }

        // Update order status to return_received
        $returnRequest->order->updateStatus(
            Order::STATUS_RETURN_RECEIVED,
            'Return item received by admin',
            'admin'
        );

        // Process refund
        $refundProcessed = $returnRequest->order->processRefund();

        if (!$refundProcessed) {
            return back()->with('error', 'Failed to process refund. Please check the order payment details.');
        }

        // Mark return request as completed
        $returnRequest->complete();

        return back()->with('success', 'Return received and refund processed successfully.');
    }

    /**
     * Get pending returns count (for AJAX requests)
     */
    public function pendingCount()
    {
        $count = ReturnRequest::where('status', ReturnRequest::STATUS_PENDING)->count();
        return response()->json(['count' => $count]);
    }
}
