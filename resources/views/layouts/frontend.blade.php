<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Meta -->
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0, maximum-scale=1"
        />
        <meta name="description" content="" />
        <meta name="keywords" content="" />
        <meta name="author" content="Gawis" />
        <!-- Page Title -->
        <title>Gawis iHerbal - Financial Wellness</title>
        <!-- Favicon Icon -->
        <link
            rel="shortcut icon"
            type="image/x-icon"
            href="{{ asset('frontend/images/favicon.png') }}?v={{ time() }}"
        />
        <!-- Google Fonts Css-->
        <link rel="preconnect" href="https://fonts.googleapis.com/" />
        <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
            rel="stylesheet"
        />
        <!-- Bootstrap Css -->
        <link href="{{ asset('frontend/css/bootstrap.min.css') }}" rel="stylesheet" media="screen" />
        <!-- SlickNav Css -->
        <link href="{{ asset('frontend/css/slicknav.min.css') }}" rel="stylesheet" />
        <!-- Swiper Css -->
        <link rel="stylesheet" href="{{ asset('frontend/css/swiper-bundle.min.css') }}" />
        <!-- Font Awesome Icon Css-->
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
            integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        />
        <!-- Animated Css -->
        <link href="{{ asset('frontend/css/animate.css') }}" rel="stylesheet" />
        <!-- Magnific Popup Core Css File -->
        <link rel="stylesheet" href="{{ asset('frontend/css/magnific-popup.css') }}" />
        <!-- Mouse Cursor Css File -->
        <link rel="stylesheet" href="{{ asset('frontend/css/mousecursor.css') }}" />
        <!-- Main Custom Css -->
        <link href="{{ asset('frontend/css/custom.css') }}" rel="stylesheet" media="screen" />
        <link href="{{ asset('frontend/css/pricelist.css') }}" rel="stylesheet" media="screen" />
        <style>
            #scrollTopBtn {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 99;
                border: 2px solid white;
                outline: none;
                background-color: transparent;
                color: white;
                cursor: pointer;
                padding: 0;
                border-radius: 50%;
                font-size: 24px;
                width: 55px;
                height: 55px;
                display: none;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                box-shadow: inset 0 0 0 1px #669933, 0 0 0 1px #669933, 0 4px 12px rgba(0, 0, 0, 0.3);
            }

            #scrollTopBtn i {
                text-shadow: -1px -1px 0 #669933, 1px -1px 0 #669933, -1px 1px 0 #669933, 1px 1px 0 #669933;
            }

            #scrollTopBtn:hover {
                background-color: rgba(255, 255, 255, 0.1);
                border-color: #669933;
                color: #669933;
                transform: translateY(-5px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
            }

            #scrollTopBtn:hover i {
                text-shadow: none;
            }
        </style>
    </head>
    <body>
        <!-- Preloader Start -->
        <div class="preloader">
            <div class="loading-container">
                <div class="loading"></div>
                <div id="loading-icon">
                    <img src="{{ asset('frontend/images/loader.png') }}" height="50" alt="" />
                </div>
            </div>
        </div>
        <!-- Preloader End -->

        <!-- Header Start -->
        <header class="main-header">
            <div class="header-sticky">
                <nav class="navbar navbar-expand-lg">
                    <div class="container">
                        <!-- Logo Start -->
                        <a class="navbar-brand" href="{{ url('/') }}">
                            <img src="{{ asset('frontend/images/logo.png') }}" height="46" alt="Logo" />
                        </a>
                        <!-- Logo End -->

                        <!-- Main Menu Start -->
                        <div class="collapse navbar-collapse main-menu">
                            <div class="nav-menu-wrapper">
                                <ul class="navbar-nav mr-auto" id="menu">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('frontend.about') }}">About</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('frontend.opportunity') }}">Opportunity</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('frontend.our-products') }}">Products</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('frontend.contact') }}">Contact</a>
                                    </li>
                                    @guest
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('login') }}">Account</a>
                                    </li>
                                    <li class="nav-item highlighted-menu">
                                        <a class="nav-link" href="{{ route('register') }}">Join Now</a>
                                    </li>
                                    @else
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                                    </li>
                                    @endguest
                                </ul>
                            </div>

                            <!-- Header Contact Btn Start -->
                            @guest
                            <div class="header-contact-btn">
                                <a href="{{ route('register') }}" class="btn-default btn-highlighted">Join Now</a>
                            </div>
                            @endguest
                            <!-- Header Contact Btn End -->
                        </div>
                        <!-- Main Menu End -->
                        <div class="navbar-toggle"></div>
                    </div>
                </nav>
                <div class="responsive-menu"></div>
            </div>
        </header>
        <!-- Header End -->

        @yield('content')

        <!-- Main Footer Start -->
        <footer class="main-footer dark-section">
            <div class="footer-overlay"></div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-5">
                        <!-- About Footer Start -->
                        <div class="about-footer">
                            <!-- Footer Logo Start -->
                            <div class="footer-logo">
                                <img src="{{ asset('frontend/images/logo.png') }}" alt="" />
                            </div>
                            <!-- Footer Logo End -->

                            <!-- About Footer Content Start -->
                            <div class="about-footer-content">
                                <p>
                                    We are committed to helping you achieve
                                    optimal health and financial wellness.
                                </p>
                            </div>
                            <!-- About Footer Content End -->
                        </div>
                        <!-- About Footer End -->
                    </div>

                    <div class="col-lg-2 col-md-3 col-6">
                        <!-- Footer Links Start -->
                        <div class="footer-links">
                            <h3>Quick link</h3>
                            <ul>                                
                                <li><a href="{{ route('frontend.about') }}">about us</a></li>
                                <li>
                                    <a href="{{ route('frontend.opportunity') }}">Opportunity</a>
                                </li>
                                <li><a href="{{ route('frontend.our-products') }}">Products</a></li>
                            </ul>
                        </div>
                        <!-- Footer Links End -->
                    </div>

                    <div class="col-lg-2 col-md-3 col-6">
                        <!-- Footer Links Start -->
                        <div class="footer-links">
                            <h3>support</h3>
                            <ul>                            
                                <li><a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Term & Condition</a></li>
                                <li><a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a></li>
                                <li><a href="{{ route('frontend.contact') }}">Contact us</a></li>
                            </ul>
                        </div>
                        <!-- Footer Links End -->
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <!-- Footer Links Start -->
                        <div class="footer-links">
                            <h3>Contact Us</h3>
                            <!-- Footer Contact Item Start -->
                            {{-- <div class="footer-contact-item">
                                <div class="icon-box">
                                    <img src="{{ asset('frontend/images/icon-phone.svg') }}" alt="" />
                                </div>
                                <div class="footer-contact-item-content">
                                    <p>Call Us</p>
                                    <h3>
                                        <a href="tel:+639876543210"
                                            ><small>+63 987 654 3210</small></a
                                        >
                                    </h3>
                                </div>
                            </div> --}}
                            <!-- Footer Contact Item End -->

                            <!-- Footer Contact Item Start -->
                            <div class="footer-contact-item">
                                <div class="icon-box">
                                    <img src="{{ asset('frontend/images/icon-mail.svg') }}" alt="" />
                                </div>
                                <div class="footer-contact-item-content">
                                    <p>E-mail</p>
                                    <h3>
                                        <a href="mailto:support@gawisherbal.com"
                                            ><small>support@gawisherbal.com</small></a
                                        >
                                    </h3>
                                </div>
                            </div>
                            <!-- Footer Contact Item End -->
                        </div>
                        <!-- Footer Links End -->
                    </div>

                    <div class="col-lg-12">
                        <!-- Footer Copyright Text Start -->
                        <div class="footer-copyright-text">
                            <p>Copyright © {{ date('Y') }} All Rights Reserved.</p>
                        </div>
                        <!-- Footer Copyright Text End -->
                    </div>
                </div>
            </div>
        </footer>
        <!-- Main Footer End -->

        <!-- Terms of Service Modal -->
        <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termsModalLabel">
                            <i class="fas fa-file-contract me-2"></i>
                            Terms of Service
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="text-center mb-4">
                                <img src="{{ asset('frontend/images/logo.png') }}" height="46" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                                <p class="text-body-secondary mt-2">E-Commerce Platform for Herbal Products</p>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <p class="text-body-secondary"><strong>Last Updated:</strong> {{ date('F d, Y') }}</p>

                                    <div class="alert alert-info">
                                        <strong>Welcome to {{ config('app.name', 'Gawis iHerbal') }}!</strong> These Terms of Service govern your use of our e-commerce platform, including product purchases, order management, returns, and integrated payment services. Please read them carefully before making any purchase.
                                    </div>

                                    <h4>1. Acceptance of Terms</h4>
                                    <p>By accessing, browsing, or using {{ config('app.name', 'Gawis iHerbal') }} ("Platform," "Service," or "Website"), you agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree with any part of these terms, you must not use our Service or make any purchases.</p>

                                    <h4>2. Description of Service</h4>
                                    <p>{{ config('app.name', 'Gawis iHerbal') }} is an e-commerce platform that provides:</p>
                                    <ul>
                                        <li><strong>Online Shopping:</strong> Browse and purchase herbal products with detailed product information</li>
                                        <li><strong>Order Management:</strong> Track orders through our comprehensive 26-status order lifecycle</li>
                                        <li><strong>Delivery Services:</strong> Choose between office pickup and home delivery options</li>
                                        <li><strong>Return & Refund Processing:</strong> Request returns within the specified timeframe with automatic refund processing</li>
                                        <li><strong>Integrated Payment System:</strong> Secure payment processing through our built-in digital wallet</li>
                                        <li><strong>Account Management:</strong> Manage your profile, delivery addresses, and order history</li>
                                    </ul>

                                    <h4>3. User Accounts</h4>
                                    <h5>3.1 Account Registration</h5>
                                    <p>To make purchases on our Platform, you must create an account by providing accurate, current, and complete information including:</p>
                                    <ul>
                                        <li>Full legal name</li>
                                        <li>Valid email address</li>
                                        <li>Secure password</li>
                                        <li>Contact phone number</li>
                                        <li>Delivery address (for home delivery orders)</li>
                                    </ul>

                                    <h5>3.2 Account Security</h5>
                                    <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to:</p>
                                    <ul>
                                        <li>Create a strong password and keep it confidential</li>
                                        <li>Notify us immediately of any unauthorized access or security breach</li>
                                        <li>Accept full responsibility for all purchases made through your account</li>
                                        <li>Enable two-factor authentication when available for enhanced security</li>
                                        <li>Log out after each session when using shared devices</li>
                                    </ul>

                                    <h5>3.3 Account Verification</h5>
                                    <p>We may require email verification before you can complete purchases. For high-value orders or delivery to new addresses, we reserve the right to request additional identity verification to prevent fraud.</p>

                                    <h4>4. Product Information and Ordering</h4>
                                    <h5>4.1 Product Listings</h5>
                                    <p>All herbal products listed on our Platform are described with the following information:</p>
                                    <ul>
                                        <li>Product name, description, and images</li>
                                        <li>Price (inclusive or exclusive of tax, as indicated)</li>
                                        <li>Available quantity and stock status</li>
                                        <li>Product specifications and ingredients</li>
                                        <li>Usage instructions and precautions</li>
                                    </ul>
                                    <p>While we make every effort to ensure accuracy, we reserve the right to correct any errors in product descriptions, pricing, or availability. If a product you ordered was incorrectly priced, we will contact you before shipping.</p>

                                    <h5>4.2 Product Availability</h5>
                                    <p>All orders are subject to product availability. We reserve the right to:</p>
                                    <ul>
                                        <li>Limit order quantities per customer</li>
                                        <li>Discontinue products without prior notice</li>
                                        <li>Refuse orders that we suspect are fraudulent or for resale purposes</li>
                                        <li>Cancel orders if products become unavailable after order placement</li>
                                    </ul>

                                    <h5>4.3 Pricing and Payment</h5>
                                    <p>All prices are displayed in the platform's currency and include or exclude tax as specified. When you place an order:</p>
                                    <ul>
                                        <li>The total amount (including tax and any applicable fees) is displayed before final confirmation</li>
                                        <li>Payment is processed instantly through your digital wallet balance</li>
                                        <li>Your wallet must have sufficient balance to complete the purchase</li>
                                        <li>Once payment is processed, the order enters our fulfillment system</li>
                                    </ul>

                                    <h5>4.4 Order Confirmation</h5>
                                    <p>After successful payment, you will receive:</p>
                                    <ul>
                                        <li>Order confirmation on-screen with unique order number</li>
                                        <li>Email confirmation with order details and tracking information</li>
                                        <li>Access to order status tracking through your account dashboard</li>
                                    </ul>

                                    <h4>5. Shipping and Delivery</h4>
                                    <h5>5.1 Delivery Methods</h5>
                                    <p>We offer two delivery options:</p>
                                    <ul>
                                        <li><strong>Office Pickup (Recommended):</strong> Collect your order from our designated pickup location. You will be notified when your order is ready for pickup.</li>
                                        <li><strong>Home Delivery:</strong> Your order will be delivered to the address provided during checkout. Delivery times vary based on location.</li>
                                    </ul>

                                    <h5>5.2 Delivery Timeframes</h5>
                                    <p>Estimated delivery timeframes are provided at checkout and are estimates only. Actual delivery may vary due to:</p>
                                    <ul>
                                        <li>Order processing time (typically 1-3 business days)</li>
                                        <li>Product availability and packaging requirements</li>
                                        <li>Your location and selected delivery method</li>
                                        <li>Weather conditions, holidays, or courier service delays</li>
                                    </ul>

                                    <h5>5.3 Delivery Address</h5>
                                    <p>You are responsible for providing accurate and complete delivery information. We are not liable for:</p>
                                    <ul>
                                        <li>Delayed or failed deliveries due to incorrect addresses</li>
                                        <li>Packages left at the delivery address as per courier instructions</li>
                                        <li>Theft or damage after successful delivery confirmation</li>
                                    </ul>

                                    <h5>5.4 Failed Delivery Attempts</h5>
                                    <p>If delivery attempts fail due to recipient unavailability or incorrect address:</p>
                                    <ul>
                                        <li>We will make up to 3 delivery attempts</li>
                                        <li>You will be contacted for redelivery arrangement</li>
                                        <li>Additional delivery fees may apply for redelivery</li>
                                        <li>Orders may be returned to our facility after failed attempts</li>
                                    </ul>

                                    <h4>6. Returns and Refunds Policy</h4>
                                    <h5>6.1 Return Eligibility</h5>
                                    <p>You may request a return of your order under the following conditions:</p>
                                    <ul>
                                        <li>Return request must be made within <strong>7 days from delivery date</strong></li>
                                        <li>Product must be in original, unopened, and resaleable condition</li>
                                        <li>Product packaging and seals must be intact</li>
                                        <li>All original accessories, manuals, and documentation must be included</li>
                                    </ul>

                                    <h5>6.2 Valid Return Reasons</h5>
                                    <p>Returns will be accepted for the following reasons:</p>
                                    <ul>
                                        <li><strong>Damaged Product:</strong> Product arrived damaged or defective</li>
                                        <li><strong>Wrong Item:</strong> You received a different product than ordered</li>
                                        <li><strong>Not as Described:</strong> Product significantly differs from listing description</li>
                                        <li><strong>Quality Issues:</strong> Product has quality defects or safety concerns</li>
                                        <li><strong>No Longer Needed:</strong> Change of mind (subject to conditions)</li>
                                        <li><strong>Other Valid Reasons:</strong> Subject to admin review</li>
                                    </ul>

                                    <h5>6.3 Non-Returnable Items</h5>
                                    <p>The following items cannot be returned for health and safety reasons:</p>
                                    <ul>
                                        <li>Opened or used herbal products</li>
                                        <li>Products with broken seals or tampered packaging</li>
                                        <li>Perishable items past their return window</li>
                                        <li>Custom or personalized orders</li>
                                        <li>Sale or clearance items (unless defective)</li>
                                    </ul>

                                    <h5>6.4 Return Process</h5>
                                    <p>To request a return:</p>
                                    <ol>
                                        <li>Log into your account and navigate to your order history</li>
                                        <li>Select the order and click "Request Return"</li>
                                        <li>Select the reason for return from the dropdown menu</li>
                                        <li>Provide a detailed description of the issue (minimum 20 characters)</li>
                                        <li>Upload photos as proof (strongly recommended for damage claims)</li>
                                        <li>Submit your return request for admin review</li>
                                        <li>Wait for approval (typically within 24 hours)</li>
                                    </ol>

                                    <h5>6.5 Return Approval and Shipping</h5>
                                    <p>Once your return request is approved:</p>
                                    <ul>
                                        <li>You will receive email notification with return shipping instructions</li>
                                        <li>Return shipping address will be provided</li>
                                        <li>You must ship the item back using a trackable shipping method</li>
                                        <li>Return shipping costs are your responsibility unless the product was defective or incorrect</li>
                                        <li>Provide tracking number through your order details page</li>
                                    </ul>

                                    <h5>6.6 Return Rejection</h5>
                                    <p>We reserve the right to reject return requests that:</p>
                                    <ul>
                                        <li>Are submitted after the 7-day return window</li>
                                        <li>Involve opened, used, or damaged products (except manufacturing defects)</li>
                                        <li>Do not meet our return eligibility criteria</li>
                                        <li>Appear to be fraudulent or abusive</li>
                                    </ul>
                                    <p>If your return is rejected, you will receive an email with the reason for rejection. The order status will revert to "Delivered" and no refund will be processed.</p>

                                    <h5>6.7 Refund Processing</h5>
                                    <p>Upon receiving and inspecting the returned item:</p>
                                    <ul>
                                        <li>We will verify the product condition within 2-3 business days</li>
                                        <li>If approved, refund will be <strong>automatically credited to your digital wallet</strong></li>
                                        <li>Refund processing is instant once confirmed</li>
                                        <li>You will receive email notification when refund is processed</li>
                                        <li>Original shipping charges are non-refundable unless we made an error</li>
                                    </ul>

                                    <h5>6.8 Partial Refunds</h5>
                                    <p>Partial refunds may be issued if:</p>
                                    <ul>
                                        <li>Item shows signs of use beyond inspection</li>
                                        <li>Item is returned without original packaging</li>
                                        <li>Item is returned after the return window but we accept it as exception</li>
                                        <li>Only some items from a multi-item order are returned</li>
                                    </ul>

                                    <h4>7. Order Cancellation</h4>
                                    <h5>7.1 Customer-Initiated Cancellation</h5>
                                    <p>You may cancel your order <strong>only if it is in "Pending" or "Paid" status</strong> and has not yet been processed. To cancel:</p>
                                    <ul>
                                        <li>Navigate to your order details page</li>
                                        <li>Click the "Cancel Order" button</li>
                                        <li>Confirm cancellation</li>
                                        <li>Refund will be automatically processed to your wallet</li>
                                    </ul>

                                    <h5>7.2 Platform-Initiated Cancellation</h5>
                                    <p>We reserve the right to cancel orders if:</p>
                                    <ul>
                                        <li>Product becomes unavailable after order placement</li>
                                        <li>Pricing or product information error was detected</li>
                                        <li>Payment verification fails</li>
                                        <li>Delivery address is unserviceable</li>
                                        <li>We suspect fraudulent activity</li>
                                    </ul>
                                    <p>If we cancel your order, full refund will be issued to your wallet, and you will be notified via email.</p>

                                    <h4>8. Digital Wallet and Payment Terms</h4>
                                    <h5>8.1 Wallet Functionality</h5>
                                    <p>Our integrated digital wallet serves as the <strong>primary payment method</strong> for all purchases. The wallet allows you to:</p>
                                    <ul>
                                        <li>Add funds via deposits (subject to admin approval)</li>
                                        <li>Make instant payments for product purchases</li>
                                        <li>Receive automatic refunds for cancelled orders and approved returns</li>
                                        <li>View complete transaction history</li>
                                        <li>Transfer funds to other users (optional feature)</li>
                                        <li>Withdraw funds to bank account (subject to verification and approval)</li>
                                    </ul>

                                    <h5>8.2 Wallet Balance and Deposits</h5>
                                    <p>To add funds to your wallet:</p>
                                    <ul>
                                        <li>Navigate to Wallet > Deposit</li>
                                        <li>Enter deposit amount and select payment method</li>
                                        <li>Submit deposit request</li>
                                        <li>Wait for admin approval (typically 1-3 business days)</li>
                                        <li>Funds will be credited to your wallet once approved</li>
                                    </ul>

                                    <h5>8.3 Payment Processing</h5>
                                    <p>When you place an order:</p>
                                    <ul>
                                        <li>System validates your wallet balance is sufficient</li>
                                        <li>Payment is deducted instantly upon order confirmation</li>
                                        <li>Transaction is recorded with unique reference number</li>
                                        <li>Payment cannot be reversed once order is confirmed</li>
                                    </ul>

                                    <h5>8.4 Transaction Fees</h5>
                                    <p>Product purchases do not incur additional payment processing fees. However, the following optional wallet operations may have fees:</p>
                                    <ul>
                                        <li>User-to-user fund transfers (if enabled)</li>
                                        <li>Wallet withdrawals to bank account</li>
                                        <li>Currency conversion (if applicable)</li>
                                    </ul>
                                    <p>All applicable fees will be clearly displayed before transaction confirmation.</p>

                                    <h5>8.5 Refund Policy</h5>
                                    <p>All refunds for cancelled orders and approved returns are processed to your digital wallet:</p>
                                    <ul>
                                        <li>Refunds are processed automatically by the system</li>
                                        <li>Refund appears instantly in your wallet balance</li>
                                        <li>Transaction record created for audit purposes</li>
                                        <li>Email notification sent upon refund completion</li>
                                        <li>Refunded amounts can be used immediately for new purchases</li>
                                    </ul>

                                    <h4>9. Product Safety and Health Disclaimers</h4>
                                    <div class="alert alert-warning">
                                        <h5 class="alert-heading">⚠️ Important Health Information</h5>
                                        <p><strong>Herbal products sold on our platform are dietary supplements and are NOT intended to diagnose, treat, cure, or prevent any disease or medical condition.</strong></p>
                                    </div>

                                    <h5>9.1 Medical Consultation</h5>
                                    <p>Before using any herbal products, you should:</p>
                                    <ul>
                                        <li>Consult with a qualified healthcare professional</li>
                                        <li>Inform your doctor about all medications and supplements you are taking</li>
                                        <li>Discuss potential interactions with existing medical conditions</li>
                                        <li>Seek medical advice if you are pregnant, nursing, or have chronic health conditions</li>
                                    </ul>

                                    <h5>9.2 Product Usage</h5>
                                    <p>You acknowledge and agree that:</p>
                                    <ul>
                                        <li>You are responsible for proper product usage according to instructions</li>
                                        <li>Results may vary from person to person</li>
                                        <li>We make no guarantees about product efficacy</li>
                                        <li>You should discontinue use and consult a doctor if adverse reactions occur</li>
                                        <li>Keep products out of reach of children</li>
                                    </ul>

                                    <h5>9.3 Regulatory Compliance</h5>
                                    <p>All products sold on our platform:</p>
                                    <ul>
                                        <li>Comply with applicable local regulations for herbal supplements</li>
                                        <li>Are not evaluated by the Food and Drug Administration (or equivalent regulatory body)</li>
                                        <li>Are sold as dietary supplements, not as medicines</li>
                                        <li>Include proper labeling with ingredients and warnings</li>
                                    </ul>

                                    <h4>10. Prohibited Activities</h4>
                                    <p>You agree NOT to:</p>
                                    <ul>
                                        <li>Use the Platform for any illegal or unauthorized purpose</li>
                                        <li>Purchase products for resale without proper business licensing</li>
                                        <li>Submit fraudulent return requests or abuse the return policy</li>
                                        <li>Provide false information during registration or checkout</li>
                                        <li>Share your account credentials with others</li>
                                        <li>Attempt to manipulate prices, inventory, or order systems</li>
                                        <li>Use automated bots or scripts to make purchases</li>
                                        <li>Transmit viruses, malware, or harmful code</li>
                                        <li>Attempt to gain unauthorized access to our systems or other user accounts</li>
                                        <li>Make medical claims about products not supported by the manufacturer</li>
                                        <li>Post false or misleading product reviews</li>
                                    </ul>

                                    <h4>11. Intellectual Property Rights</h4>
                                    <p>All content on the Platform, including but not limited to:</p>
                                    <ul>
                                        <li>Website design, layout, and user interface</li>
                                        <li>Product images, descriptions, and listings</li>
                                        <li>Logos, trademarks, and branding materials</li>
                                        <li>Software, source code, and algorithms</li>
                                        <li>Text content, graphics, and multimedia</li>
                                    </ul>
                                    <p>...is the property of {{ config('app.name', 'Gawis iHerbal') }} or its licensors and is protected by intellectual property laws. You may not copy, reproduce, distribute, or create derivative works without our express written permission.</p>

                                    <h4>12. Privacy and Data Protection</h4>
                                    <p>Your privacy is important to us. We collect, use, and protect your information as described in our <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>. By using our Service, you consent to:</p>
                                    <ul>
                                        <li>Collection of personal information for order processing</li>
                                        <li>Storage of delivery addresses and contact information</li>
                                        <li>Processing of payment and transaction data</li>
                                        <li>Use of cookies and tracking technologies</li>
                                        <li>Communication regarding your orders and account</li>
                                    </ul>

                                    <h4>13. Disclaimers and Limitation of Liability</h4>
                                    <h5>13.1 Service Availability</h5>
                                    <p>While we strive to provide uninterrupted service, we cannot guarantee 100% uptime. We are not liable for:</p>
                                    <ul>
                                        <li>Temporary service outages or maintenance downtime</li>
                                        <li>Technical issues preventing order placement or payment processing</li>
                                        <li>Third-party service failures (payment gateways, courier services)</li>
                                        <li>Force majeure events beyond our control</li>
                                    </ul>

                                    <h5>13.2 Product Availability</h5>
                                    <p>Product listings are subject to change without notice. We are not liable for:</p>
                                    <ul>
                                        <li>Products becoming unavailable after you place an order</li>
                                        <li>Pricing errors or incorrect product information</li>
                                        <li>Product variations from images or descriptions</li>
                                    </ul>

                                    <h5>13.3 Limitation of Liability</h5>
                                    <p>To the maximum extent permitted by law, {{ config('app.name', 'Gawis iHerbal') }} and its officers, directors, employees, and agents shall not be liable for:</p>
                                    <ul>
                                        <li>Any indirect, incidental, special, consequential, or punitive damages</li>
                                        <li>Loss of profits, revenue, data, or business opportunities</li>
                                        <li>Damages arising from product use or adverse reactions</li>
                                        <li>Damages from delivery delays, failed deliveries, or courier errors</li>
                                        <li>Damages from unauthorized account access due to your negligence</li>
                                    </ul>
                                    <p>Our total liability to you for any claim shall not exceed the amount you paid for the specific product or service giving rise to the claim.</p>

                                    <h4>14. Indemnification</h4>
                                    <p>You agree to indemnify, defend, and hold harmless {{ config('app.name', 'Gawis iHerbal') }}, its affiliates, officers, directors, employees, and agents from any claims, liabilities, damages, losses, or expenses (including legal fees) arising from:</p>
                                    <ul>
                                        <li>Your use of the Platform or purchased products</li>
                                        <li>Violation of these Terms of Service</li>
                                        <li>Violation of any laws or third-party rights</li>
                                        <li>Fraudulent or abusive behavior</li>
                                        <li>Improper product use or misrepresentation</li>
                                    </ul>

                                    <h4>15. Account Termination and Suspension</h4>
                                    <p>We reserve the right to terminate or suspend your account immediately, without prior notice or liability, if:</p>
                                    <ul>
                                        <li>You violate these Terms of Service</li>
                                        <li>We suspect fraudulent or illegal activity</li>
                                        <li>You abuse the return policy or engage in return fraud</li>
                                        <li>Multiple payment failures or chargebacks occur</li>
                                        <li>You engage in harassment or harmful behavior toward staff or other users</li>
                                    </ul>
                                    <p>Upon termination:</p>
                                    <ul>
                                        <li>Your access to the Platform will be revoked</li>
                                        <li>Pending orders will be cancelled and refunded</li>
                                        <li>You may request withdrawal of your wallet balance (subject to verification)</li>
                                        <li>We reserve the right to withhold funds pending investigation of suspected fraud</li>
                                    </ul>

                                    <h4>16. Dispute Resolution</h4>
                                    <h5>16.1 Customer Support</h5>
                                    <p>For any issues or concerns, please contact our customer support team first. We are committed to resolving disputes amicably through direct communication.</p>

                                    <h5>16.2 Arbitration</h5>
                                    <p>Any disputes arising from these Terms or your use of the Platform shall be resolved through binding arbitration rather than in court, except where prohibited by law. The arbitration will be conducted under the rules of [Applicable Arbitration Association].</p>

                                    <h5>16.3 Class Action Waiver</h5>
                                    <p>You agree to resolve disputes on an individual basis only. You waive any right to participate in class action lawsuits or class-wide arbitration.</p>

                                    <h4>17. Governing Law and Jurisdiction</h4>
                                    <p>These Terms shall be governed by and construed in accordance with the laws of [Your Jurisdiction], without regard to conflict of law provisions. You agree to submit to the exclusive jurisdiction of the courts located in [Your Jurisdiction] for resolution of any disputes.</p>

                                    <h4>18. Changes to Terms of Service</h4>
                                    <p>We reserve the right to modify these Terms at any time. Significant changes will be communicated through:</p>
                                    <ul>
                                        <li>Email notification to registered users</li>
                                        <li>Prominent notice on the Platform</li>
                                        <li>Update to "Last Updated" date at the top of this document</li>
                                    </ul>
                                    <p>Your continued use of the Platform after changes are made constitutes acceptance of the new Terms. If you do not agree with the changes, you must stop using the Platform and close your account.</p>

                                    <h4>19. Severability</h4>
                                    <p>If any provision of these Terms is found to be invalid or unenforceable, the remaining provisions shall remain in full force and effect. The invalid provision will be modified to the minimum extent necessary to make it valid and enforceable.</p>

                                    <h4>20. Entire Agreement</h4>
                                    <p>These Terms of Service, together with our Privacy Policy, constitute the entire agreement between you and {{ config('app.name', 'Gawis iHerbal') }} regarding use of the Platform and supersede all prior agreements and understandings.</p>

                                    <h4>21. Contact Information</h4>
                                    <p>For questions, concerns, or support regarding these Terms of Service, please contact us:</p>
                                    @php
                                        $adminUser = \App\Models\User::role('admin')->first();
                                    @endphp
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="mb-1"><strong>{{ config('app.name', 'Gawis iHerbal') }}</strong></p>
                                            @if($adminUser)
                                                <p class="mb-1">Email: {{ $adminUser->email }}</p>
                                                @if($adminUser->phone)
                                                    <p class="mb-1">Phone: {{ $adminUser->phone }}</p>
                                                @endif
                                                @if($adminUser->address)
                                                    <p class="mb-0">
                                                        Address: {{ $adminUser->address }}
                                                        @if($adminUser->address_2), {{ $adminUser->address_2 }}@endif
                                                        @if($adminUser->city), {{ $adminUser->city }}@endif
                                                        @if($adminUser->state), {{ $adminUser->state }}@endif
                                                        @if($adminUser->zip) {{ $adminUser->zip }}@endif
                                                    </p>
                                                @endif
                                            @else
                                                <p class="mb-1">Email: admin@gawisiherbal.com</p>
                                                <p class="mb-0">Please contact us via email for assistance.</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="alert alert-success mt-4">
                                        <strong>Thank you for shopping with {{ config('app.name', 'Gawis iHerbal') }}!</strong> We're committed to providing you with high-quality herbal products, excellent customer service, and a secure shopping experience. Your trust is our priority.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy Policy Modal -->
        <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="privacyModalLabel">
                            <i class="fas fa-shield-alt me-2"></i>
                            Privacy Policy
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="text-center mb-4">
                                <img src="{{ asset('frontend/images/logo.png') }}" height="46" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                                <p class="text-body-secondary mt-2">E-Commerce Platform for Herbal Products</p>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <p class="text-body-secondary"><strong>Last Updated:</strong> {{ date('F d, Y') }}</p>

                                    <div class="alert alert-info">
                                        <strong>Your Privacy is Our Priority!</strong> This Privacy Policy explains how {{ config('app.name', 'Gawis iHerbal') }} collects, uses, protects, and shares your personal information when you shop on our e-commerce platform. We are committed to protecting your privacy and complying with applicable data protection laws.
                                    </div>

                                    <h4>1. Information We Collect</h4>

                                    <h5>1.1 Account and Registration Information</h5>
                                    <p>When you create an account to shop on our platform, we collect:</p>
                                    <ul>
                                        <li><strong>Personal Identifiers:</strong> Full name, username, email address</li>
                                        <li><strong>Contact Information:</strong> Phone number, contact preferences</li>
                                        <li><strong>Account Credentials:</strong> Encrypted password and security settings</li>
                                        <li><strong>Profile Information:</strong> Profile picture (optional), date of birth (if provided)</li>
                                        <li><strong>Verification Data:</strong> Email verification status, account verification documents (if required)</li>
                                    </ul>

                                    <h5>1.2 Delivery and Shipping Information</h5>
                                    <p>For order fulfillment and delivery, we collect:</p>
                                    <ul>
                                        <li><strong>Delivery Address:</strong> Street address, city, state/province, postal code, country</li>
                                        <li><strong>Recipient Information:</strong> Recipient name and contact number</li>
                                        <li><strong>Delivery Preferences:</strong> Preferred delivery method (office pickup or home delivery)</li>
                                        <li><strong>Special Instructions:</strong> Delivery notes, time preferences, accessibility information</li>
                                        <li><strong>Address History:</strong> Previously used addresses for convenience</li>
                                    </ul>

                                    <h5>1.3 Purchase and Order Information</h5>
                                    <p>When you make purchases, we collect:</p>
                                    <ul>
                                        <li><strong>Shopping Cart Data:</strong> Products added, quantities, prices</li>
                                        <li><strong>Order Details:</strong> Order numbers, purchase dates, item descriptions</li>
                                        <li><strong>Order Status:</strong> Processing stage, delivery tracking, order history</li>
                                        <li><strong>Package Preferences:</strong> Product selections, special requests</li>
                                        <li><strong>Customer Notes:</strong> Any special instructions or comments provided during checkout</li>
                                    </ul>

                                    <h5>1.4 Payment and Transaction Information</h5>
                                    <p>For payment processing through our integrated digital wallet:</p>
                                    <ul>
                                        <li><strong>Wallet Balance:</strong> Current wallet balance and transaction history</li>
                                        <li><strong>Payment Transactions:</strong> Transaction amounts, dates, times, reference numbers</li>
                                        <li><strong>Deposit Information:</strong> Deposit requests, payment method used for deposits</li>
                                        <li><strong>Refund Records:</strong> Refund transactions for cancelled orders or approved returns</li>
                                        <li><strong>Banking Details:</strong> Bank account information for withdrawals (if applicable)</li>
                                    </ul>
                                    <p><strong>Note:</strong> We do not directly store credit card numbers or sensitive payment card data. External payment processors handle card transactions securely.</p>

                                    <h5>1.5 Return and Refund Information</h5>
                                    <p>When you request returns or refunds, we collect:</p>
                                    <ul>
                                        <li><strong>Return Reason:</strong> Category and detailed description of return reason</li>
                                        <li><strong>Proof Images:</strong> Photos uploaded as evidence for return claims</li>
                                        <li><strong>Communication Records:</strong> Messages exchanged with customer support regarding returns</li>
                                        <li><strong>Return Shipping Data:</strong> Return tracking numbers and shipping information</li>
                                        <li><strong>Refund Processing:</strong> Refund status, amounts, and transaction records</li>
                                    </ul>

                                    <h5>1.6 Technical and Device Information</h5>
                                    <p>We automatically collect certain technical data:</p>
                                    <ul>
                                        <li><strong>Device Information:</strong> Device type, model, operating system, browser version</li>
                                        <li><strong>Usage Data:</strong> Pages viewed, time spent, click patterns, navigation paths</li>
                                        <li><strong>IP Address:</strong> Your device's Internet Protocol address</li>
                                        <li><strong>Location Data:</strong> Approximate geographic location based on IP address</li>
                                        <li><strong>Session Information:</strong> Login times, session duration, activity logs</li>
                                        <li><strong>Cookies and Tracking:</strong> See Section 10 for detailed cookie information</li>
                                    </ul>

                                    <h5>1.7 Customer Support and Communications</h5>
                                    <p>When you contact us for support, we may collect:</p>
                                    <ul>
                                        <li>Customer support inquiries and correspondence</li>
                                        <li>Chat transcripts and email communications</li>
                                        <li>Phone call recordings (with notice and consent)</li>
                                        <li>Feedback, reviews, and survey responses</li>
                                        <li>Issue reports and resolution history</li>
                                    </ul>

                                    <h4>2. How We Use Your Information</h4>

                                    <h5>2.1 Order Processing and Fulfillment</h5>
                                    <p>We use your information to:</p>
                                    <ul>
                                        <li>Process and fulfill your product orders</li>
                                        <li>Arrange delivery or pickup of purchased items</li>
                                        <li>Generate order confirmations, invoices, and receipts</li>
                                        <li>Track order status and provide delivery updates</li>
                                        <li>Manage inventory and product availability</li>
                                        <li>Create package snapshots for order records</li>
                                    </ul>

                                    <h5>2.2 Payment Processing</h5>
                                    <p>We use your payment information to:</p>
                                    <ul>
                                        <li>Process payments for purchases through your digital wallet</li>
                                        <li>Validate wallet balance before order confirmation</li>
                                        <li>Process refunds for cancelled orders and approved returns</li>
                                        <li>Manage wallet deposits and withdrawals</li>
                                        <li>Maintain transaction records for accounting and audit purposes</li>
                                        <li>Prevent payment fraud and unauthorized transactions</li>
                                    </ul>

                                    <h5>2.3 Return and Refund Management</h5>
                                    <p>We process your information to:</p>
                                    <ul>
                                        <li>Handle return requests and evaluate eligibility</li>
                                        <li>Review return reasons and supporting documentation</li>
                                        <li>Communicate return approval or rejection decisions</li>
                                        <li>Track returned items during shipping</li>
                                        <li>Process automatic wallet refunds upon return confirmation</li>
                                        <li>Maintain return/refund records for quality improvement</li>
                                    </ul>

                                    <h5>2.4 Account Management</h5>
                                    <p>We use your information to:</p>
                                    <ul>
                                        <li>Create and maintain your user account</li>
                                        <li>Authenticate your identity during login</li>
                                        <li>Enable two-factor authentication for security</li>
                                        <li>Manage your profile and delivery address preferences</li>
                                        <li>Provide access to order history and wallet information</li>
                                        <li>Reset passwords and recover accounts</li>
                                    </ul>

                                    <h5>2.5 Customer Support</h5>
                                    <p>We use your information to:</p>
                                    <ul>
                                        <li>Respond to inquiries and support requests</li>
                                        <li>Resolve issues with orders, deliveries, or returns</li>
                                        <li>Provide technical assistance and troubleshooting</li>
                                        <li>Investigate and address complaints</li>
                                        <li>Follow up on customer satisfaction</li>
                                    </ul>

                                    <h5>2.6 Marketing and Promotions</h5>
                                    <p>With your consent, we may use your information to:</p>
                                    <ul>
                                        <li>Send promotional emails about new products and special offers</li>
                                        <li>Notify you of sales, discounts, and seasonal promotions</li>
                                        <li>Recommend products based on your purchase history</li>
                                        <li>Send newsletters and product updates</li>
                                        <li>Conduct market research and surveys</li>
                                    </ul>
                                    <p><strong>Opt-Out:</strong> You can unsubscribe from marketing emails at any time using the unsubscribe link in emails or through your account settings.</p>

                                    <h5>2.7 Platform Improvement</h5>
                                    <p>We analyze information to:</p>
                                    <ul>
                                        <li>Improve website functionality and user experience</li>
                                        <li>Optimize product catalog and search features</li>
                                        <li>Analyze shopping trends and customer preferences</li>
                                        <li>Test new features and gather feedback</li>
                                        <li>Monitor platform performance and uptime</li>
                                        <li>Identify and fix bugs or technical issues</li>
                                    </ul>

                                    <h5>2.8 Security and Fraud Prevention</h5>
                                    <p>We use your information to:</p>
                                    <ul>
                                        <li>Detect and prevent fraudulent transactions</li>
                                        <li>Monitor for suspicious account activity</li>
                                        <li>Verify identity for high-value orders</li>
                                        <li>Protect against return fraud and policy abuse</li>
                                        <li>Secure payment processing and wallet transactions</li>
                                        <li>Investigate security incidents and breaches</li>
                                    </ul>

                                    <h5>2.9 Legal Compliance</h5>
                                    <p>We may use your information to:</p>
                                    <ul>
                                        <li>Comply with applicable laws and regulations</li>
                                        <li>Respond to legal requests from authorities</li>
                                        <li>Enforce our Terms of Service and policies</li>
                                        <li>Resolve disputes and legal claims</li>
                                        <li>Maintain records as required by law</li>
                                        <li>Report suspicious activities to regulatory bodies</li>
                                    </ul>

                                    <h4>3. Information Sharing and Disclosure</h4>

                                    <h5>3.1 Service Providers and Partners</h5>
                                    <p>We share information with trusted third-party service providers who assist with:</p>
                                    <ul>
                                        <li><strong>Shipping and Logistics:</strong> Courier services for order delivery (name, address, phone number)</li>
                                        <li><strong>Payment Processing:</strong> Payment gateways for deposit processing (limited payment data)</li>
                                        <li><strong>Cloud Hosting:</strong> Servers and database hosting providers</li>
                                        <li><strong>Email Services:</strong> Email delivery platforms for notifications</li>
                                        <li><strong>Customer Support Tools:</strong> Help desk and ticketing systems</li>
                                        <li><strong>Analytics Providers:</strong> Website analytics and usage tracking</li>
                                    </ul>
                                    <p>These providers are contractually obligated to protect your data and use it only for specified purposes.</p>

                                    <h5>3.2 Business Transfers</h5>
                                    <p>If {{ config('app.name', 'Gawis iHerbal') }} is involved in a merger, acquisition, or sale of assets, your information may be transferred to the new owner. We will notify you before your information is transferred and becomes subject to a different privacy policy.</p>

                                    <h5>3.3 Legal Requirements</h5>
                                    <p>We may disclose your information if required by law or in response to:</p>
                                    <ul>
                                        <li>Court orders, subpoenas, or legal processes</li>
                                        <li>Government investigations or regulatory requests</li>
                                        <li>National security or law enforcement requirements</li>
                                        <li>Protection of our legal rights and interests</li>
                                        <li>Prevention of fraud, crime, or harm to individuals</li>
                                    </ul>

                                    <h5>3.4 With Your Consent</h5>
                                    <p>We may share information with third parties when you explicitly consent, such as:</p>
                                    <ul>
                                        <li>Connecting with social media platforms</li>
                                        <li>Participating in partner promotions</li>
                                        <li>Sharing feedback or testimonials publicly</li>
                                        <li>Integrating with third-party apps or services</li>
                                    </ul>

                                    <h5>3.5 Aggregate and De-identified Data</h5>
                                    <p>We may share aggregate, statistical, or de-identified data that cannot identify you personally for:</p>
                                    <ul>
                                        <li>Industry research and trend analysis</li>
                                        <li>Marketing and advertising purposes</li>
                                        <li>Business intelligence and reporting</li>
                                        <li>Public disclosure of platform statistics</li>
                                    </ul>

                                    <h4>4. Data Security</h4>

                                    <h5>4.1 Security Measures</h5>
                                    <p>We implement comprehensive security measures to protect your information:</p>
                                    <ul>
                                        <li><strong>Encryption:</strong> SSL/TLS encryption for data transmission, encrypted storage for sensitive data</li>
                                        <li><strong>Access Controls:</strong> Role-based access, employee authentication, principle of least privilege</li>
                                        <li><strong>Password Protection:</strong> Bcrypt hashing, password complexity requirements</li>
                                        <li><strong>Two-Factor Authentication:</strong> Optional 2FA for enhanced account security</li>
                                        <li><strong>Firewall Protection:</strong> Network security, intrusion detection systems</li>
                                        <li><strong>Regular Audits:</strong> Security assessments, vulnerability scanning, penetration testing</li>
                                        <li><strong>Secure Backups:</strong> Encrypted backups with restricted access</li>
                                        <li><strong>Staff Training:</strong> Regular security awareness training for employees</li>
                                    </ul>

                                    <h5>4.2 Your Security Responsibilities</h5>
                                    <p>You play a crucial role in protecting your account:</p>
                                    <ul>
                                        <li>Use strong, unique passwords</li>
                                        <li>Enable two-factor authentication</li>
                                        <li>Keep login credentials confidential</li>
                                        <li>Log out after using shared devices</li>
                                        <li>Report suspicious activity immediately</li>
                                        <li>Keep your contact information updated</li>
                                    </ul>

                                    <h5>4.3 Data Breach Notification</h5>
                                    <p>In the event of a data breach that compromises your personal information:</p>
                                    <ul>
                                        <li>We will notify affected users within 72 hours of discovery</li>
                                        <li>Notification will be sent via email to your registered address</li>
                                        <li>We will provide details about the breach and affected data</li>
                                        <li>We will advise on protective measures you can take</li>
                                        <li>We will report to relevant regulatory authorities as required</li>
                                    </ul>

                                    <h4>5. Data Retention</h4>

                                    <h5>5.1 Retention Periods</h5>
                                    <p>We retain your information for different periods based on purpose:</p>
                                    <ul>
                                        <li><strong>Account Information:</strong> Duration of account plus 3 years after closure</li>
                                        <li><strong>Order Records:</strong> 7 years for tax and accounting purposes</li>
                                        <li><strong>Payment Transactions:</strong> 7 years for financial compliance</li>
                                        <li><strong>Return/Refund Records:</strong> 5 years for dispute resolution</li>
                                        <li><strong>Customer Support Records:</strong> 3 years for quality assurance</li>
                                        <li><strong>Marketing Preferences:</strong> Until you withdraw consent or account closure</li>
                                        <li><strong>Technical Logs:</strong> 90 days for security monitoring</li>
                                    </ul>

                                    <h5>5.2 Account Deletion</h5>
                                    <p>Upon account deletion request:</p>
                                    <ul>
                                        <li>Active account data will be anonymized or deleted</li>
                                        <li>Transaction records may be retained for legal compliance</li>
                                        <li>Some data may be retained in backup systems temporarily</li>
                                        <li>De-identified data may be retained for analytics</li>
                                    </ul>

                                    <h4>6. Your Privacy Rights</h4>

                                    <h5>6.1 Access and Correction</h5>
                                    <p>You have the right to:</p>
                                    <ul>
                                        <li>Access your personal information held by us</li>
                                        <li>Request corrections to inaccurate or outdated information</li>
                                        <li>Update your profile and delivery address information</li>
                                        <li>Review your order history and transaction records</li>
                                    </ul>

                                    <h5>6.2 Data Portability</h5>
                                    <p>You can request:</p>
                                    <ul>
                                        <li>A copy of your personal data in machine-readable format</li>
                                        <li>Export of your order history and transaction records</li>
                                        <li>Transfer of data to another service provider (where technically feasible)</li>
                                    </ul>

                                    <h5>6.3 Deletion and Erasure</h5>
                                    <p>You can request deletion of your information, subject to:</p>
                                    <ul>
                                        <li>Legal obligations requiring data retention</li>
                                        <li>Ongoing transactions or pending orders</li>
                                        <li>Dispute resolution needs</li>
                                        <li>Security and fraud prevention requirements</li>
                                    </ul>

                                    <h5>6.4 Marketing Opt-Out</h5>
                                    <p>You can opt out of marketing communications:</p>
                                    <ul>
                                        <li>Click "Unsubscribe" in promotional emails</li>
                                        <li>Update email preferences in account settings</li>
                                        <li>Contact customer support to opt out</li>
                                    </ul>
                                    <p><strong>Note:</strong> You will still receive transactional emails (order confirmations, shipping notifications, account alerts) even if you opt out of marketing.</p>

                                    <h5>6.5 Complaint Rights</h5>
                                    <p>If you believe we have mishandled your personal information:</p>
                                    <ul>
                                        <li>Contact our Data Protection Officer (see Section 12)</li>
                                        <li>File a complaint with your local data protection authority</li>
                                        <li>Seek legal remedies as provided by applicable law</li>
                                    </ul>

                                    <h4>7. Children's Privacy</h4>
                                    <p>Our platform is not intended for children under 18 years of age. We do not knowingly collect personal information from children. If you are under 18:</p>
                                    <ul>
                                        <li>Do not create an account or make purchases</li>
                                        <li>Do not provide any personal information on the platform</li>
                                        <li>Do not submit product reviews or communications</li>
                                    </ul>
                                    <p>If we discover we have collected information from a child under 18, we will promptly delete such information. Parents or guardians who believe their child has provided information to us should contact us immediately.</p>

                                    <h4>8. International Data Transfers</h4>
                                    <p>Your information may be transferred to and processed in countries other than your country of residence. When we transfer data internationally:</p>
                                    <ul>
                                        <li>We ensure adequate data protection measures are in place</li>
                                        <li>We use standard contractual clauses approved by regulatory authorities</li>
                                        <li>We comply with applicable cross-border data transfer regulations</li>
                                        <li>We notify you if data will be transferred to countries with different privacy laws</li>
                                    </ul>

                                    <h4>9. Third-Party Links</h4>
                                    <p>Our platform may contain links to third-party websites (e.g., courier tracking, social media). Please note:</p>
                                    <ul>
                                        <li>This Privacy Policy does not apply to third-party websites</li>
                                        <li>We are not responsible for third-party privacy practices</li>
                                        <li>Review the privacy policies of websites you visit</li>
                                        <li>Third-party services have their own data collection practices</li>
                                    </ul>

                                    <h4>10. Cookies and Tracking Technologies</h4>

                                    <h5>10.1 Types of Cookies We Use</h5>
                                    <ul>
                                        <li><strong>Essential Cookies:</strong> Required for platform functionality (authentication, cart management)</li>
                                        <li><strong>Performance Cookies:</strong> Help us understand platform usage and performance</li>
                                        <li><strong>Functional Cookies:</strong> Remember your preferences and settings</li>
                                        <li><strong>Analytics Cookies:</strong> Track user behavior for improvement insights</li>
                                        <li><strong>Marketing Cookies:</strong> Deliver personalized advertisements (with consent)</li>
                                    </ul>

                                    <h5>10.2 Managing Cookies</h5>
                                    <p>You can control cookies through:</p>
                                    <ul>
                                        <li>Browser settings to block or delete cookies</li>
                                        <li>Opt-out tools provided by analytics services</li>
                                        <li>Platform cookie consent preferences (if available)</li>
                                    </ul>
                                    <p><strong>Note:</strong> Disabling essential cookies may affect platform functionality.</p>

                                    <h4>11. Changes to This Privacy Policy</h4>
                                    <p>We may update this Privacy Policy from time to time. When we make changes:</p>
                                    <ul>
                                        <li>The "Last Updated" date will be revised</li>
                                        <li>Material changes will be communicated via email</li>
                                        <li>Prominent notice will be displayed on the platform</li>
                                        <li>Continued use after changes indicates acceptance</li>
                                    </ul>
                                    <p>We encourage you to review this Privacy Policy periodically to stay informed about how we protect your information.</p>

                                    <h4>12. Contact Us</h4>
                                    <p>For questions, concerns, or requests regarding this Privacy Policy or your personal information, please contact:</p>

                                    @php
                                        $adminUser = \App\Models\User::role('admin')->first();
                                    @endphp
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="mb-1"><strong>{{ config('app.name', 'Gawis iHerbal') }}</strong></p>
                                            @if($adminUser)
                                                <p class="mb-1">Email: {{ $adminUser->email }}</p>
                                                @if($adminUser->phone)
                                                    <p class="mb-1">Phone: {{ $adminUser->phone }}</p>
                                                @endif
                                                @if($adminUser->address)
                                                    <p class="mb-0">
                                                        Address: {{ $adminUser->address }}
                                                        @if($adminUser->address_2), {{ $adminUser->address_2 }}@endif
                                                        @if($adminUser->city), {{ $adminUser->city }}@endif
                                                        @if($adminUser->state), {{ $adminUser->state }}@endif
                                                        @if($adminUser->zip) {{ $adminUser->zip }}@endif
                                                    </p>
                                                @endif
                                            @else
                                                <p class="mb-1">Email: privacy@gawisiherbal.com</p>
                                                <p class="mb-0">Please contact us via email for privacy-related inquiries.</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6 class="card-title">Response Time</h6>
                                            <p class="mb-0">We will respond to privacy-related requests within 30 days of receipt. For urgent security concerns, contact us immediately through our support channels.</p>
                                        </div>
                                    </div>

                                    <div class="alert alert-success mt-4">
                                        <strong>Thank you for trusting {{ config('app.name', 'Gawis iHerbal') }} with your personal information.</strong> We are committed to protecting your privacy and providing a secure shopping experience. Your data security and privacy rights are our top priorities.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jquery Library File -->
        <script src="{{ asset('frontend/js/jquery-3.7.1.min.js') }}"></script>
        <!-- Bootstrap js file -->
        <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
        <!-- Validator js file -->
        <script src="{{ asset('frontend/js/validator.min.js') }}"></script>
        <!-- SlickNav js file -->
        <script src="{{ asset('frontend/js/jquery.slicknav.js') }}"></script>
        <!-- Swiper js file -->
        <script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
        <!-- Counter js file -->
        <script src="{{ asset('frontend/js/jquery.waypoints.min.js') }}"></script>
        <script src="{{ asset('frontend/js/jquery.counterup.min.js') }}"></script>
        <!-- Magnific js file -->
        <script src="{{ asset('frontend/js/jquery.magnific-popup.min.js') }}"></script>
        <!-- SmoothScroll -->
        <script src="{{ asset('frontend/js/SmoothScroll.js') }}"></script>
        <!-- Parallax js -->
        <script src="{{ asset('frontend/js/parallaxie.js') }}"></script>
        <!-- MagicCursor js file -->
        <script src="{{ asset('frontend/js/gsap.min.js') }}"></script>
        <script src="{{ asset('frontend/js/magiccursor.js') }}"></script>
        <!-- Text Effect js file -->
        <script src="{{ asset('frontend/js/SplitText.js') }}"></script>
        <script src="{{ asset('frontend/js/ScrollTrigger.min.js') }}"></script>
        <!-- YTPlayer js File -->
        <script src="{{ asset('frontend/js/jquery.mb.YTPlayer.min.js') }}"></script>
        <!-- Wow js file -->
        <script src="{{ asset('frontend/js/wow.min.js') }}"></script>
        <!-- Main Custom js file -->
        <script src="{{ asset('frontend/js/function.js') }}?v={{ time() }}"></script>

        <!-- Scroll to Top Button -->
        <button id="scrollTopBtn" title="Go to top">
            <i class="fas fa-arrow-up"></i>
        </button>

        <script>
            $(document).ready(function() {
                var $scrollBtn = $("#scrollTopBtn");

                $(window).on('scroll', function() {
                    if ($(this).scrollTop() > 300) {
                        $scrollBtn.css('display', 'flex');
                    } else {
                        $scrollBtn.css('display', 'none');
                    }
                });

                $scrollBtn.on('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: "smooth",
                    });
                });
            });
        </script>
        @stack('scripts')
    </body>
</html>