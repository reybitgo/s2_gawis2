# Frontend Integration Plan

This document outlines the phased approach to integrate the static frontend design from `/public/frontend` into the main Laravel application, making it the default landing experience.

## Phase 1: Initial Route and View Setup

**Goal:** Make the content of `public/frontend/index.html` render at the root URL (`/`) of the Laravel application.

1.  **Create a New View:**
    *   Copy the entire content of `public/frontend/index.html`.
    *   Create a new Blade view file at `resources/views/frontend/index.blade.php`.
    *   Paste the copied HTML content into this new Blade file.

2.  **Modify the Main Web Route:**
    *   Open the `routes/web.php` file.
    *   Locate the existing route for `/`. It likely redirects to `/login` or returns a welcome view.
    *   Modify this route to return the new Blade view. The new route should be:
        ```php
        Route::get('/', function () {
            return view('frontend.index');
        });
        ```

**Outcome:** Visiting the application's root URL will display the raw, unstyled HTML from the frontend design. Assets like CSS and JavaScript will be broken.

---

## Phase 2: Asset Integration

**Goal:** Correctly link all CSS, JavaScript, and image assets so the frontend design renders perfectly.

1.  **Update Asset Paths in the Blade View:**
    *   Edit the `resources/views/frontend/index.blade.php` file.
    *   Search for all asset links (e.g., `<link>`, `<script>`, `<img>` tags).
    *   Update their `href` or `src` attributes to use Laravel's `asset()` helper function, pointing to the correct path within the `public` directory.
    *   For example, a link like `href="css/style.css"` should be changed to `href="{{ asset('frontend/css/style.css') }}"`.
    *   Similarly, an image like `src="images/logo.png"` becomes `src="{{ asset('frontend/images/logo.png') }}"`.

**Outcome:** The root URL will now display the frontend design with all styling, scripts, and images correctly loaded, looking identical to the original static `index.html`.

---

## Phase 3: Dynamic Route Integration

**Goal:** Replace static placeholder links in the frontend design with dynamic links to the Laravel application's routes (e.g., login, register).

1.  **Identify Dynamic Links:**
    *   Review the `resources/views/frontend/index.blade.php` file.
    *   Identify all `<a>` tags that should point to internal application pages, such as "Login", "Register", "Shop", "My Account", etc.

2.  **Update Links with the `route()` Helper:**
    *   Replace the static `href` attributes with Laravel's `route()` helper function.
    *   For example, `<a href="login.html">` should be changed to `<a href="{{ route('login') }}">`.
    *   `<a href="register.html">` should be changed to `<a href="{{ route('register') }}">`.

3.  **Implement Conditional Authentication Links:**
    *   Use Blade's `@auth` and `@guest` directives to show different links based on the user's authentication status.
    *   For example:
        ```blade
        @guest
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
        @endguest
        @auth
            <a href="{{ route('dashboard') }}">My Dashboard</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                    Logout
                </a>
            </form>
        @endauth
        ```

**Outcome:** The frontend will be interactive, allowing users to navigate to the login, registration, and other dynamic pages of the Laravel application.

---

## Phase 4: Create a Reusable Frontend Layout

**Goal:** To avoid code duplication and make the frontend scalable for new pages, extract the common header, footer, and structure into a Blade layout file.

1.  **Create a Layout File:**
    *   Create a new file at `resources/views/layouts/frontend.blade.php`.
    *   Copy the basic HTML structure (including `<html>`, `<head>`, `<body>`, header, footer, and common scripts/styles) from `resources/views/frontend/index.blade.php` into this new layout file.

2.  **Define a Content Section:**
    *   In the layout file, at the place where the main page content should be, add `@yield('content')`.

3.  **Update the Index View to Use the Layout:**
    *   Modify `resources/views/frontend/index.blade.php`.
    *   Remove all the common HTML that was moved to the layout file.
    *   At the top of the file, add `@extends('layouts.frontend')`.
    *   Wrap the remaining page-specific content in a `@section('content')` block.

**Outcome:** A clean, reusable layout system is in place. New frontend pages can now be created easily by extending the `layouts.frontend` Blade file.

---

## Phase 5: Cleanup and Finalization

**Goal:** Remove redundant files and ensure the project structure is clean.

1.  **Review and Verify:**
    *   Thoroughly test the integration. Ensure the homepage works, all assets load, and all dynamic links navigate correctly.

2.  **Remove Obsolete HTML Files:**
    *   The static HTML files in `/public/frontend` (like `index.html`, `about.html`, etc.) are now redundant.
    *   Delete these HTML files to prevent confusion.
    *   **Important:** Do NOT delete the asset directories (`/public/frontend/css`, `/public/frontend/js`, `/public/frontend/images`, etc.) as they are still being used by the application.

**Outcome:** The project is fully integrated with the new frontend, with a clean and maintainable structure.
