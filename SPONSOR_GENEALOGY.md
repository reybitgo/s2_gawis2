# Plan: Unified Member Genealogy View

This document outlines a revised, comprehensive plan to create a unified Member Genealogy View. This plan accounts for the new requirement to display context-specific earnings (MLM vs. Unilevel) on each node and focuses on a reusable, efficient, and maintainable codebase.

**Core Principle:** The sponsorship tree is identical for both MLM and Unilevel. We will build a single, powerful backend service and a single frontend component to render the tree, and then display it in two separate views with different data overlays (MLM earnings vs. Unilevel earnings).

**Responsive Design Mandate:** A primary goal of the frontend implementation (Phases 3-5) is to ensure the genealogy view is perfectly responsive and mobile-ready. The design must adapt gracefully to all screen sizes, from small mobile phones to large desktop monitors, providing an optimal viewing experience for all users.

---

### Phase 1: Enhanced Backend Service (The Data Engine)

**Goal:** Create a single, highly performant service that can fetch the complete user hierarchy and overlay it with context-specific earnings data in an efficient manner.

1.  **Create a Test Case:**
    *   In `tests/Feature/GenealogyTest.php`, create test methods for both MLM and Unilevel scenarios.
    *   The tests will seed a user hierarchy, along with sample `mlm_commissions` and `unilevel_bonuses` transactions linking a logged-in user to their downlines.
    *   The tests will assert that the final data structure returned by the service contains the correct users at the correct levels **and** that each node has the correct, aggregated earnings amount attached.

2.  **Create/Enhance the Genealogy Service:**
    *   Create the `app/Services/GenealogyService.php`.
    *   The primary public method will be `getGenealogyTree(User $user, string $earningsType)`. The `$earningsType` will be either `'mlm'` or `'unilevel'`. 

3.  **Implement High-Performance Data Retrieval:**
    *   The service will execute a **two-step query process** for maximum performance:
        a.  **Step 1 (Get Tree Structure):** Use a **Recursive Common Table Expression (CTE)** raw query to fetch all downline user IDs and their levels in a single database call.
        b.  **Step 2 (Get Earnings Data):** With the list of downline IDs from the CTE, run a **second, single query** against the appropriate transactions table. This query will `SUM` the earnings and `GROUP BY` the `buyer_id` (or equivalent) to get a total earnings amount from each downline member for the logged-in user. The query will be chosen based on the `$earningsType` parameter.
    *   **Step 3 (Merge Data):** In PHP, efficiently merge the earnings data into the user hierarchy structure. The result is a single, rich data tree.

4.  **Create the Controller:**
    *   Create `app/Http/Controllers/Member/GenealogyController.php`.
    *   Create two methods: `showUnilevel()` and `showMlm()`.
    *   Each method will call the `GenealogyService` with the correct `$earningsType` (`'unilevel'` or `'mlm'`).

**Outcome:** A fully tested, reusable, and highly performant backend service that can generate a complete genealogy tree for either MLM or Unilevel contexts, with accurate earnings data attached to each node.

---

### Phase 2: Dual Routes and Views

**Goal:** Create the separate pages for MLM and Unilevel genealogy and add links for the user to access them.

1.  **Create Routes:**
    *   In `routes/web.php`, add two distinct routes within the authenticated member middleware group:
        *   `Route::get('/member/unilevel/genealogy', [GenealogyController::class, 'showUnilevel'])->name('member.unilevel.genealogy');`
        *   `Route::get('/member/mlm/genealogy', [GenealogyController::class, 'showMlm'])->name('member.mlm.genealogy');`

2.  **Create Parent Views:**
    *   Create two parent Blade files:
        *   `resources/views/member/genealogy/unilevel.blade.php`
        *   `resources/views/member/genealogy/mlm.blade.php`
    *   Both views will extend the main app layout and have a title (e.g., "Unilevel Genealogy").

3.  **Update Controller Methods:**
    *   Modify the `showUnilevel` and `showMlm` methods in `GenealogyController` to pass the data from the service to their respective Blade views.

4.  **Add Dashboard Links:**
    *   On the main member dashboard, add **two** separate links:
        *   One for "Unilevel Genealogy" pointing to `route('member.unilevel.genealogy')`.
        *   One for "MLM Genealogy" pointing to `route('member.mlm.genealogy')`.

**Outcome:** Two distinct URLs and pages are created. A user can navigate to either page, and the correct data is being prepared by the controller, though not yet fully rendered.

---

### Phase 3: Unified & Responsive Recursive Component

**Goal:** Build a single, intelligent, and responsive frontend component that can render any node in the tree and adapt its layout for mobile and desktop screens.

1.  **Create a Blade Component:**
    *   Create a single, reusable component: `resources/views/components/genealogy-node.blade.php`.
    *   The component's internal structure will use Flexbox to arrange the member's details (name, earnings, status), allowing them to wrap naturally on smaller screens.

2.  **Implement Component Logic:**
    *   The component will accept props: `$member`, `$earningsLabel`, and `$earningsValue`.
    *   It will display the member's info and earnings.
    *   It will recursively call itself for any children within a nested `<ul>` list, which is semantic and stylable.

3.  **Implement Responsive CSS:**
    *   A dedicated CSS block or file will be created for the genealogy tree.
    *   **Desktop Styles:** Use a larger `padding-left` on nested `<li>` elements to create a clear, indented tree structure.
    *   **Mobile Styles:** Use `@media` queries to detect smaller screens. On mobile, the `padding-left` for indentation will be significantly reduced to conserve horizontal space and prevent content from being cut off.

4.  **Update Parent Views:**
    *   In both `unilevel.blade.php` and `mlm.blade.php`, use a loop to call the `<x-genealogy-node>` component for the first-level downlines, passing the appropriate data and labels.

**Outcome:** Both genealogy pages render a complete, static, 5-level tree that is visually optimized for both desktop and mobile viewing. The underlying component is built with responsive principles from the start.

---

### Phase 4: Frontend Interactivity (Expand/Collapse)

**Goal:** Make the tree easy to navigate by adding expand and collapse functionality.

1.  **Implement JavaScript:**
    *   Add a small, efficient JavaScript snippet to the parent views or a global asset file.
    *   The script will hide all but the first level of the tree on page load.
    *   It will add click listeners to each node that has children.
    *   Clicking a node will toggle the visibility of its children.
    *   Add `+`/`-` icons to indicate the collapsed/expanded state of each node.

**Outcome:** Both genealogy trees are now fully interactive and user-friendly, starting in a collapsed state and allowing for easy exploration.

---

### Phase 5: Advanced Features & Final Polish

**Goal:** Implement the final requirements from the documentation and ensure the feature is robust, polished, and fully responsive.

1.  **Implement Color-Coding:**
    *   In the `genealogy-node` component, add a dynamic CSS class based on the member's status (`active`, `inactive`, `suspended`) to visually distinguish them.

2.  **Implement Search:**
    *   Add a search input to both the `unilevel.blade.php` and `mlm.blade.php` views.
    *   Use JavaScript to filter the tree in real-time based on user input (name or username), hiding non-matching nodes while keeping their parent hierarchy visible.

3.  **Display Statistics:**
    *   Enhance the `GenealogyService` to also return summary statistics (total members, active members per level).
    *   The controller will pass this data to the views.
    *   Display these statistics in a summary box at the top of each genealogy page.

4.  **Final Testing and Review:**
    *   **Responsive Testing:** Conduct thorough manual testing on a wide range of screen sizes, from small mobile phones to large desktops, using browser developer tools to emulate devices. Ensure all elements are readable, tappable, and correctly aligned on all tested screen sizes.
    *   **Functional Testing:** Run all automated tests and manually verify that all features (tree, interactivity, search, stats, styling) work perfectly and the data is accurate.
    *   **Performance Testing:** Verify that the implementation remains performant, even with a large downline structure.

**Outcome:** A complete, performant, and feature-rich genealogy tool that is fully responsive and meets all user requirements for both Unilevel and MLM contexts.