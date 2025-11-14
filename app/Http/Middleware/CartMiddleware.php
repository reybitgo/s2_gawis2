<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CartMiddleware
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Validate cart items to ensure they're still available
        $this->cartService->validateCart();

        // Get cart summary for use in views
        $cartSummary = $this->cartService->getSummary();

        // Share cart data with all views
        View::share('globalCartCount', $cartSummary['item_count']);
        View::share('globalCartTotal', $cartSummary['total']);
        View::share('globalCartSummary', $cartSummary);

        return $next($request);
    }
}
