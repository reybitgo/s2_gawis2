<!-- Terms of Service Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">
                    <svg class="icon icon-lg me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-description') }}"></use>
                    </svg>
                    Terms of Service
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="text-center mb-4">
                        <img class="logo-dark" src="{{ asset('coreui-template/assets/brand/gawis_logo.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                        <img class="logo-light" src="{{ asset('coreui-template/assets/brand/gawis_logo_light.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                        <p class="text-body-secondary mt-2">E-Commerce Platform for Herbal Products</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <p class="text-body-secondary"><strong>Last Updated:</strong> {{ date('F d, Y') }}</p>

                            <div class="alert alert-info border-0" style="background-color: var(--cui-info-bg-subtle); color: var(--cui-info-text-emphasis); border-color: var(--cui-info-border-subtle);">
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
                            <div class="alert alert-warning border-0" style="background-color: var(--cui-warning-bg-subtle); color: var(--cui-warning-text-emphasis); border-color: var(--cui-warning-border-subtle);">
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
                            <p>Your privacy is important to us. We collect, use, and protect your information as described in our <a href="#" data-coreui-toggle="modal" data-coreui-target="#privacyModal">Privacy Policy</a>. By using our Service, you consent to:</p>
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
                            <div class="card border-0" style="background-color: var(--cui-tertiary-bg); border-color: var(--cui-border-color);">
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

                            <div class="alert alert-success border-0 mt-4" style="background-color: var(--cui-success-bg-subtle); color: var(--cui-success-text-emphasis); border-color: var(--cui-success-border-subtle);">
                                <strong>Thank you for shopping with {{ config('app.name', 'Gawis iHerbal') }}!</strong> We're committed to providing you with high-quality herbal products, excellent customer service, and a secure shopping experience. Your trust is our priority.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="acceptTerms()">I Accept These Terms</button>
            </div>
        </div>
    </div>
</div>

<script>
function acceptTerms() {
    // Check the terms checkbox if it exists
    const termsCheckbox = document.getElementById('terms');
    if (termsCheckbox) {
        termsCheckbox.checked = true;
    }
    // Close the modal
    const modal = coreui.Modal.getInstance(document.getElementById('termsModal'));
    if (modal) {
        modal.hide();
    }
}
</script>
