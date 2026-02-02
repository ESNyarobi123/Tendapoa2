<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Terms and Conditions - Tendapoa</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: #f8fafc;
        }

        /* Header & Nav (Copied from home.blade.php) */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 800;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #2563eb;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: #1e3a8a;
            color: white;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.3);
        }

        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.4);
        }

        /* Page Content */
        .page-header {
            padding-top: 120px;
            padding-bottom: 3rem;
            background: white;
            text-align: center;
        }

        .page-title {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .content-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .policy-card {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .policy-section {
            margin-bottom: 3rem;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 3rem;
        }

        .policy-section:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .section-header {
            display: flex;
            align-items: baseline;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .section-number {
            font-size: 3rem;
            font-weight: 800;
            color: #e2e8f0;
            line-height: 1;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .policy-content p {
            margin-bottom: 1rem;
            color: #475569;
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .policy-content ul {
            list-style: none;
            margin-left: 0;
            margin-bottom: 1.5rem;
        }

        .policy-content li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.75rem;
            color: #475569;
        }

        .policy-content li::before {
            content: "•";
            color: #2563eb;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .subsection {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .subsection h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.75rem;
        }

        /* Footer (Copied from home.blade.php) */
        .footer {
            background: #1e293b;
            color: white;
            padding: 4rem 2rem 2rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 4rem;
            margin-bottom: 2rem;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: white;
        }

        .footer-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .footer-nav h3 {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 0.5rem;
            display: inline-block;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .policy-card {
                padding: 1.5rem;
            }

            .section-header {
                flex-direction: column;
                gap: 0.5rem;
            }

            .section-number {
                font-size: 2rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <a href="/" class="logo">
                <span style="font-size: 2rem;">🧹</span>
                <span>Tendapoa</span>
            </a>
            <div class="nav-links">
                <a href="/#home">Nyumbani</a>
                <a href="/#services">Huduma</a>
                <a href="/#about">Kuhusu</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Pata Huduma</a>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">TENDAPOA – TERMS AND CONDITIONS OF USE</h1>
            <p class="page-subtitle">Last Update 1 February 2026</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-container">
        <div class="policy-card">

            <p style="margin-bottom: 2rem; color: #475569; font-size: 1.05rem;">
                Welcome to Tendapoa. These Terms and Conditions (“Terms”) govern your access to and use of the Tendapoa
                mobile application, website, and all related services (collectively, the “Platform”).<br><br>
                By accessing, registering, or using Tendapoa, you acknowledge that you have read, understood, and agreed
                to be bound by these Terms.
            </p>

            <!-- 1. About Tendapoa -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">01</span>
                    <h2 class="section-title">About Tendapoa</h2>
                </div>
                <div class="policy-content">
                    <p>Tendapoa is a digital marketplace that connects independent cleanliness and laundry service
                        providers (“Service Providers”) with individuals or organizations seeking such services
                        (“Customers”).</p>
                    <p>Tendapoa does not provide cleaning, laundry, or physical services directly and does not employ
                        Service Providers.</p>
                </div>
            </div>

            <!-- 2. Eligibility -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">02</span>
                    <h2 class="section-title">Eligibility</h2>
                </div>
                <div class="policy-content">
                    <p>To use Tendapoa, you must:</p>
                    <ul>
                        <li>Be at least 18 years old</li>
                        <li>Have legal capacity to enter into binding agreements</li>
                        <li>Provide accurate, complete, and up-to-date registration information</li>
                    </ul>
                    <p>Tendapoa reserves the right to suspend or terminate accounts that provide false, misleading, or
                        incomplete information.</p>
                </div>
            </div>

            <!-- 3. User Accounts -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">03</span>
                    <h2 class="section-title">User Accounts</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Users must create an account to access the Platform.</li>
                        <li>You are responsible for safeguarding your login credentials.</li>
                        <li>All activities conducted through your account are your responsibility.</li>
                        <li>Tendapoa is not liable for unauthorized access resulting from your failure to secure your
                            account.</li>
                    </ul>
                </div>
            </div>

            <!-- 4. Roles and Responsibilities -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">04</span>
                    <h2 class="section-title">Roles and Responsibilities</h2>
                </div>
                <div class="policy-content">
                    <div class="subsection">
                        <h3>4.1 Customers</h3>
                        <p>Customers may:</p>
                        <ul>
                            <li>Post service requests with clear descriptions and budgets</li>
                            <li>Pay the agreed service amount upfront via the Platform</li>
                            <li>Confirm service completion using a completion code</li>
                        </ul>
                        <p>Customers must:</p>
                        <ul>
                            <li>Provide accurate and truthful job details</li>
                            <li>Release payment only after satisfactory service completion</li>
                        </ul>
                    </div>

                    <div class="subsection">
                        <h3>4.2 Service Providers</h3>
                        <p>Service Providers may:</p>
                        <ul>
                            <li>Accept or reject job requests</li>
                            <li>Set their own service prices</li>
                            <li>Withdraw earnings subject to Platform rules</li>
                        </ul>
                        <p>Service Providers must:</p>
                        <ul>
                            <li>Deliver services professionally and as agreed</li>
                            <li>Comply with all applicable laws and regulations</li>
                            <li>Pay Tendapoa’s commission as required</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 5. Payments, Wallet & Commission -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">05</span>
                    <h2 class="section-title">Payments, Wallet & Commission</h2>
                </div>
                <div class="policy-content">
                    <div class="subsection">
                        <h3>5.1 Upfront Payment & Escrow</h3>
                        <ul>
                            <li>Customers must pay the full agreed amount before a job is posted.</li>
                            <li>Funds are held securely in escrow until job completion is confirmed.</li>
                        </ul>
                    </div>

                    <div class="subsection">
                        <h3>5.2 Completion Code</h3>
                        <ul>
                            <li>After service completion, Customers provide a completion code.</li>
                            <li>Only after code confirmation will funds be credited to the Service Provider’s wallet.
                            </li>
                        </ul>
                    </div>

                    <div class="subsection">
                        <h3>5.3 Commission</h3>
                        <ul>
                            <li>Tendapoa charges a 10% commission on each completed job.</li>
                            <li>The commission is automatically deducted before wallet credit.</li>
                        </ul>
                    </div>

                    <div class="subsection">
                        <h3>5.4 Withdrawals</h3>
                        <ul>
                            <li>Minimum withdrawal amount: TZS 5,000</li>
                            <li>Withdrawal fee: TZS 500 per transaction</li>
                            <li>Withdrawals are subject to verification and processing timelines.</li>
                        </ul>
                    </div>

                    <div class="subsection">
                        <h3>5.5 Third-Party Payments</h3>
                        <ul>
                            <li>All payments are processed through third-party payment service providers.</li>
                            <li>Tendapoa does not store users’ card details, mobile money credentials, or banking
                                information.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 6. Cancellations & Refunds -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">06</span>
                    <h2 class="section-title">Cancellations & Refunds</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Customers may cancel a job before service commencement.</li>
                        <li>Refund eligibility depends on service status, timing, and Platform assessment.</li>
                        <li>Tendapoa may deduct applicable administrative or transaction fees.</li>
                        <li>Refund decisions are final, subject to applicable consumer protection laws.</li>
                    </ul>
                </div>
            </div>

            <!-- 7. Ratings & Reviews -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">07</span>
                    <h2 class="section-title">Ratings & Reviews</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Users may rate and review each other after job completion.</li>
                        <li>Reviews must be honest, respectful, and factual.</li>
                        <li>Tendapoa reserves the right to remove abusive, false, or misleading content.</li>
                    </ul>
                </div>
            </div>

            <!-- 8. Prohibited Conduct -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">08</span>
                    <h2 class="section-title">Prohibited Conduct</h2>
                </div>
                <div class="policy-content">
                    <p>Users must not:</p>
                    <ul>
                        <li>Engage in fraud, harassment, abuse, or misrepresentation</li>
                        <li>Bypass Tendapoa’s payment or escrow system</li>
                        <li>Share or misuse completion codes</li>
                        <li>Use the Platform for unlawful purposes</li>
                    </ul>
                    <p>Violation may result in account suspension or permanent termination.</p>
                </div>
            </div>

            <!-- 9. Limitation of Liability -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">09</span>
                    <h2 class="section-title">Limitation of Liability</h2>
                </div>
                <div class="policy-content">
                    <p>To the maximum extent permitted by law:</p>
                    <ul>
                        <li>Tendapoa is not responsible for service quality, delays, or disputes between users.</li>
                        <li>Tendapoa is not liable for personal injury, property damage, theft, or loss arising from
                            services performed by Service Providers.</li>
                        <li>Tendapoa does not guarantee uninterrupted or error-free access to the Platform.</li>
                        <li>Tendapoa shall not be liable for indirect, incidental, or consequential damages.</li>
                    </ul>
                </div>
            </div>

            <!-- 10. Dispute Resolution -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">10</span>
                    <h2 class="section-title">Dispute Resolution</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Users are encouraged to resolve disputes amicably.</li>
                        <li>Tendapoa may assist but is not obligated to arbitrate or resolve disputes.</li>
                        <li>Tendapoa does not guarantee dispute resolution outcomes.</li>
                    </ul>
                    <p>These Terms are governed by the laws of the United Republic of Tanzania.</p>
                </div>
            </div>

            <!-- 11. Intellectual Property -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">11</span>
                    <h2 class="section-title">Intellectual Property</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>All content, trademarks, logos, software, and materials on Tendapoa are the exclusive
                            property of Tendapoa.</li>
                        <li>Unauthorized use, reproduction, or distribution is prohibited.</li>
                    </ul>
                </div>
            </div>

            <!-- 12. Account Suspension & Termination -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">12</span>
                    <h2 class="section-title">Account Suspension & Termination</h2>
                </div>
                <div class="policy-content">
                    <p>Tendapoa reserves the right to:</p>
                    <ul>
                        <li>Suspend or terminate accounts that violate these Terms</li>
                        <li>Temporarily hold wallet balances during investigations of suspicious activity</li>
                    </ul>
                    <p>Any remaining wallet balance may be refunded or released in accordance with applicable laws after
                        investigation.</p>
                </div>
            </div>

            <!-- 13. Amendments -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">13</span>
                    <h2 class="section-title">Amendments</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Tendapoa may update these Terms at any time.</li>
                        <li>Continued use of the Platform constitutes acceptance of the revised Terms.</li>
                    </ul>
                </div>
            </div>

            <!-- 14. Contact Information -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">14</span>
                    <h2 class="section-title">Contact Information</h2>
                </div>
                <div class="policy-content">
                    <p>For inquiries, complaints, or support:</p>
                    <p><strong>Tendapoa Support</strong><br>
                        📧 Email: support@tendapoa.com<br>
                        📞 Phone: +255 626 957 138</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div>
                <div class="footer-logo">🧹 Tendapoa</div>
                <p class="footer-description">Tendapoa inatoa suluhisho bora za usafi, kubadilisha nafasi kuwa maeneo
                    safi kupitia uangalifu wa pekee na kujitolea kwa ubora.</p>
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.875rem;">Hakimiliki © {{ date('Y') }} Tendapoa.
                    Haki zote zimehifadhiwa.</p>
            </div>
            <div>
                <h3>Navigation</h3>
                <div class="footer-links">
                    <a href="/#home">Nyumbani</a>
                    <a href="/#services">Huduma</a>
                    <a href="/#about">Kuhusu</a>
                    <a href="{{ route('policy.fees-payments') }}">Sera ya Malipo & Ada</a>
                    <a href="{{ route('policy.terms') }}" style="color: white; font-weight: 600;">Terms & Conditions</a>
                    <a href="{{ route('register') }}">Pata Huduma</a>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>