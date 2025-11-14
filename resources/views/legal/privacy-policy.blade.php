<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">
                    <svg class="icon icon-lg me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                    </svg>
                    Privacy Policy
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
                            <div class="card border-0 mb-3" style="background-color: var(--cui-tertiary-bg); border-color: var(--cui-border-color);">
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

                            <div class="card border-0" style="background-color: var(--cui-info-bg-subtle); border-color: var(--cui-info-border-subtle);">
                                <div class="card-body">
                                    <h6 class="card-title">Response Time</h6>
                                    <p class="mb-0">We will respond to privacy-related requests within 30 days of receipt. For urgent security concerns, contact us immediately through our support channels.</p>
                                </div>
                            </div>

                            <div class="alert alert-success border-0 mt-4" style="background-color: var(--cui-success-bg-subtle); color: var(--cui-success-text-emphasis); border-color: var(--cui-success-border-subtle);">
                                <strong>Thank you for trusting {{ config('app.name', 'Gawis iHerbal') }} with your personal information.</strong> We are committed to protecting your privacy and providing a secure shopping experience. Your data security and privacy rights are our top priorities.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
