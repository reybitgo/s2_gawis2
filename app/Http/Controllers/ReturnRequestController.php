<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReturnRequestController extends Controller
{
    /**
     * Store a new return request
     */
    public function store(Request $request, Order $order)
    {
        // Verify order belongs to authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Check if order can be returned
        if (!$order->canRequestReturn()) {
            return back()->with('error', 'This order is not eligible for return.');
        }

        $validated = $request->validate([
            'reason' => 'required|in:damaged_product,wrong_item,not_as_described,quality_issue,no_longer_needed,other',
            'description' => 'required|string|min:20|max:1000',
            'images.*' => 'nullable|image|max:2048', // Max 2MB per image
        ]);

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('return-requests', 'public');
                $imagePaths[] = $path;
            }
        }

        // Create return request
        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'images' => $imagePaths,
            'status' => ReturnRequest::STATUS_PENDING,
        ]);

        // Update order status to return_requested
        $order->updateStatus(
            Order::STATUS_RETURN_REQUESTED,
            'Customer requested return: ' . ReturnRequest::getReasonLabels()[$validated['reason']],
            'customer'
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Return request submitted successfully. We\'ll review it within 24 hours.');
    }

    /**
     * Update return tracking number
     */
    public function updateTracking(Request $request, ReturnRequest $returnRequest)
    {
        // Verify return request belongs to authenticated user
        if ($returnRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this return request.');
        }

        // Verify return request is approved
        if (!$returnRequest->isApproved()) {
            return back()->with('error', 'Return tracking can only be updated for approved returns.');
        }

        $validated = $request->validate([
            'return_tracking_number' => 'required|string|max:255',
        ]);

        $returnRequest->updateTrackingNumber($validated['return_tracking_number']);

        return back()->with('success', 'Return tracking number updated successfully.');
    }
}
