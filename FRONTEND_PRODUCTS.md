### Phase 1: Homepage - Displaying Top-Selling Products

This phase focuses on fetching and displaying the four best-selling products on the homepage.

1.  **Data-Layer Investigation:** I will first examine the `OrderItem` model and its database migration to understand how product sales are recorded. This will allow me to build a query that accurately calculates the total quantity sold for each product.
2.  **Update `FrontendController`:** I will modify the `index` method in `app/Http/Controllers/FrontendController.php`. A new query will be added to fetch the top four products, ordered by their total quantity sold. This data will then be passed to the `index.blade.php` view.
3.  **Update Homepage View:** I will update the "Our Products" section in `resources/views/frontend/index.blade.php` to:
    *   Dynamically display the four products fetched from the controller, replacing the current hardcoded content.
    *   Ensure the product display is consistent with the existing site design.
    *   Update the "View All Products" button to link to the `frontend.our-products` route.

### Phase 2: "Our Products" Page - Backend and Frontend Enhancement

This phase focuses on enhancing the dedicated `/our-products` page to display all available products. The only task is to update the product list, just like what was done in Phase 1. No modifications will be made to the prevailing CSS of the host page `resources/views/frontend/products.blade.php`.

1.  **Enhance `FrontendController`:** I will modify the `products` method in `app/Http/Controllers/FrontendController.php`. The method will be updated to retrieve all active products from the database.
2.  **Update Products View:** I will modify the existing `resources/views/frontend/products.blade.php` view to:
    *   Loop through the products passed from the controller to dynamically display them.

### Phase 3: Documentation

Upon completion of the implementation, I will create a `FRONTEND_PRODUCTS.md` file. This document will contain a complete summary of the changes made, the logic used to determine best-selling products, and instructions for managing the new product display features.