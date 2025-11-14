# Frontend Development Blueprint: Gawis iHerbal

## 1. Project Vision & Goals

The primary goal is to evolve the platform from a backend-focused application to a complete online presence with a public-facing website. This website will serve as the digital storefront and recruitment hub for Gawis iHerbal.

**Core Objectives:**
- **Build Brand Trust:** Establish a professional, trustworthy brand image that communicates quality and reliability.
- **Attract Customers:** Showcase products effectively to drive online sales.
- **Recruit Distributors:** Clearly and compellingly present the business opportunity to attract new members.
- **Educate & Engage:** Provide valuable content about products and the MLM opportunity to build a loyal community.

## 2. Inspiration & Core Principles

This blueprint is inspired by the successful online presences of leading wellness MLM companies like **doTERRA, Young Living, and 4Life Research**. The design and content strategy should adhere to these core principles:

- **Product-First:** The quality and benefits of the products are the foundation. The website should feel like a premium wellness brand first, and an MLM opportunity second.
- **Clear & Inspiring Opportunity:** The business opportunity should be presented with clarity, transparency, and inspiration. It must be easy to understand how to get started and what the potential is.
- **Trust Through Science & Story:** Build credibility by sharing the story behind the company and the science behind the products.
- **Community-Centric:** Foster a sense of community by highlighting success stories and providing educational content.

## 3. Site Architecture & Key Pages

A visitor should be able to navigate the site and understand the entire business without needing to log in. The main navigation should include:

- **Home (`/`)**: The main landing page.
- **Shop (`/products`)**: The e-commerce storefront.
- **Our Story (`/about`)**: The company mission, story, and commitment to quality.
- **The Opportunity (`/join`)**: Detailed explanation of the MLM business opportunity.
- **Blog / Community (`/blog`)**: Educational articles and community stories.
- **Contact (`/contact`)**: Contact information and support form.

User authentication links (`Login`, `Register`) should be clearly visible in the header.

## 4. Detailed Page-by-Page Breakdown

### 4.1. Homepage (`/`)
**Purpose:** To capture interest immediately and guide visitors to either shop for products or learn about the business opportunity.

- **Hero Section:**
  - **Visual:** High-quality, professional lifestyle video or image showcasing people enjoying a healthy, vibrant life.
  - **Headline:** A powerful, benefit-oriented headline (e.g., "Experience Natural Wellness. Discover Financial Freedom.").
  - **CTAs:** Two prominent, distinct buttons: `Shop Products` (primary) and `Join Our Mission` (secondary).

- **Featured Products Section:**
  - A curated grid of 3-4 best-selling products.
  - Each product should have a high-quality image, name, price, and a quick `View Details` button.

- **The Gawis iHerbal Difference (Trust Building):**
  - A section with three iconic columns:
    - **Pure & Natural Ingredients:** Emphasize quality and sourcing.
    - **Scientifically Formulated:** Highlight the research and care put into the products.
    - **Community & Empowerment:** Introduce the idea of the supportive community.

- **Discover the Opportunity (Teaser Section):**
  - A brief, engaging section with a headline like "More Than a Product. It's a Partnership."
  - Briefly mention benefits like "Earn from Home," "Be Your Own Boss," and "Join a Supportive Community."
  - **CTA:** A single button: `Learn How to Earn` linking to the `/join` page.

- **Testimonials Section:**
  - Showcase a slider with quotes and photos from two types of people:
    1.  **Product Users:** Focusing on the health benefits they've experienced.
    2.  **Business Builders:** Focusing on how the opportunity has changed their lives.

- **Footer:**
  - Standard navigation links, social media icons, and a newsletter signup form to capture leads.

### 4.2. Shop Page (`/products`)
**Purpose:** To provide a seamless and intuitive shopping experience.

- **Layout:** A clean, modern grid of products.
- **Sidebar Filters:**
  - **Categories:** (e.g., Beauty, Health & Wellness)
  - **Health Goals:** (e.g., Immunity, Energy, Skin Care)
  - **Price Range:** A slider or predefined ranges.
- **Sorting Options:** Dropdown to sort by `Popularity`, `Newest`, `Price: Low to High`, `Price: High to Low`.
- **Product Cards:**
  - Professional product image.
  - Product Name, Price, and Points Awarded.
  - "Quick View" option and an "Add to Cart" button.

### 4.3. The Opportunity Page (`/join`)
**Purpose:** To clearly, professionally, and persuasively explain the MLM opportunity and guide prospects to sign up.

- **Hero Section:**
  - **Visual:** An inspiring image or video of a diverse group of successful, happy people.
  - **Headline:** "Start Your Journey to Wellness and Financial Independence Today."
  - **CTA:** A prominent `Join Now` button that scrolls down to the Starter Package section.

- **How It Works (Simplified 3-Step Visual):**
  - An infographic or section with three clear steps:
    1.  **Become a Member:** Purchase our Starter Package to unlock your business.
    2.  **Share & Earn:** Share your love for the products using your personal referral link.
    3.  **Grow Your Team:** Earn commissions and bonuses as your network grows.

- **The Compensation Plan (Transparent & Simple):**
  - A clean, easy-to-understand infographic explaining the 5-level Unilevel commission on the Starter Package and the separate Unilevel bonus on products.
  - **Crucial:** Include a clear and visible **Income Disclaimer** (e.g., "*Earnings are not guaranteed and depend on individual effort, skill, and sales volume."). This is essential for legal compliance and building trust.

- **Choose Your Starter Package:**
  - Prominently feature the **Starter Package** with its price, contents, and the benefits it unlocks.
  - **CTA:** A large `Become a Member for ₱1,000` button.

- **Success Stories / Testimonials:**
  - Feature 2-3 detailed stories from successful distributors. Video testimonials are highly effective here.

- **FAQ Section:**
  - An accordion-style FAQ addressing common questions like: "Do I need to be a sales expert?", "Is there a monthly purchase requirement?", "How do I get paid?"

### 4.4. Our Story Page (`/about`)
**Purpose:** To build a deep connection with the visitor by sharing the company's mission and commitment to quality.

- **Our Mission:** A heartfelt statement about why the company was founded.
- **Commitment to Quality (The Science Section):**
  - Detail the process from sourcing ingredients to manufacturing.
  - Mention quality control, testing standards, and certifications (if any). This builds immense trust, similar to doTERRA's "CPTG" standard.
- **Meet the Founders/Leadership:** (Optional) Professional photos and brief bios to humanize the company.

## 5. Design & User Experience (UX) Principles

- **Visual Style:** Clean, bright, and modern. Use a soft and natural color palette (greens, blues, earthy tones) to reflect the wellness theme.
- **Typography:** Use clear, legible fonts. A combination of a modern sans-serif for body text and an elegant serif for headlines works well.
- **Imagery:** Invest in high-quality, professional photography and videography. Avoid generic stock photos.
- **Responsiveness:** The website must be fully responsive and optimized for a seamless experience on mobile devices, tablets, and desktops.
- **Performance:** Fast load times are critical. Optimize images and use modern frontend technologies to ensure a snappy user experience.

## 6. Recommended Technical Stack

- **Framework:** **Next.js (React)** or **Nuxt.js (Vue)**. These frameworks are ideal for their Server-Side Rendering (SSR) and Static Site Generation (SSG) capabilities, which are crucial for SEO and performance.
- **Styling:** **Tailwind CSS**. It's a utility-first framework that allows for rapid development and highly maintainable styles, consistent with the current backend views.
- **State Management:** React Context/Redux or Vuex for managing global state like the shopping cart and user authentication.
- **Deployment:** Vercel or Netlify for easy, fast, and scalable frontend hosting.
