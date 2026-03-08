<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title>Privacy Policy - Tendapoa</title>

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

        /* Header & Nav */
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

        /* Footer */
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
                <a href="<?php echo e(route('register')); ?>" class="btn btn-primary">Pata Huduma</a>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">TENDAPOA – PRIVACY POLICY</h1>
            <p class="page-subtitle">Last Update 8 March 2026</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-container">
        <div class="policy-card">

            <p style="margin-bottom: 2rem; color: #475569; font-size: 1.05rem;">
                Welcome to Tendapoa. This Privacy Policy outlines how we collect, use, disclose, and safeguard your personal information when you use the Tendapoa mobile application, website, and related services (collectively, the “Platform”).<br><br>
                By accessing, registering, or using Tendapoa, you consent to the data practices described in this Privacy Policy.
            </p>

            <!-- 1. Information We Collect -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">01</span>
                    <h2 class="section-title">Information We Collect</h2>
                </div>
                <div class="policy-content">
                    <p>We collect various types of information in connection with the services we provide, including:</p>
                    <div class="subsection">
                        <h3>1.1 Personal Information</h3>
                        <ul>
                            <li><strong>Account details:</strong> Name, phone number, email address, password, and profile picture.</li>
                            <li><strong>Financial information:</strong> Payment history, withdrawal requests, and transaction records. (Note: We use third-party providers for actual payment processing and do not store sensitive card or banking credentials).</li>
                            <li><strong>Identity Verification:</strong> Identification details required to verify Service Providers.</li>
                        </ul>
                    </div>
                    <div class="subsection">
                        <h3>1.2 Usage and Technical Data</h3>
                        <ul>
                            <li>Device information (e.g., hardware model, operating system).</li>
                            <li>Log information (e.g., IP address, access times, browser type).</li>
                            <li>Information about how you interact with our Platform, including jobs posted, accepted, or completed.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 2. How We Use Your Information -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">02</span>
                    <h2 class="section-title">How We Use Your Information</h2>
                </div>
                <div class="policy-content">
                    <p>We use the collected information for various purposes, including to:</p>
                    <ul>
                        <li>Facilitate the matching of Service Providers and Customers.</li>
                        <li>Process payments, issue completion codes, and manage the escrow system securely.</li>
                        <li>Maintain and improve the safety, security, and performance of our Platform.</li>
                        <li>Provide user support, resolve disputes, and respond to your requests.</li>
                        <li>Send service updates, promotional offers, and system notifications.</li>
                        <li>Enforce our Terms and Conditions and prevent fraudulent activities.</li>
                    </ul>
                </div>
            </div>

            <!-- 3. Sharing Your Information -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">03</span>
                    <h2 class="section-title">Sharing Your Information</h2>
                </div>
                <div class="policy-content">
                    <p>We do not sell your personal information. We may share your information under the following circumstances:</p>
                    <ul>
                        <li><strong>Between Users:</strong> Customers and Service Providers may see each other's basic profile details (e.g., name, ratings, and contact info) to facilitate the requested service.</li>
                        <li><strong>Service Providers:</strong> With third-party vendors, consultants, and payment processors who need access to such information to carry out work on our behalf.</li>
                        <li><strong>Legal Obligations:</strong> If required to do so by law or in response to valid requests by public authorities (e.g., a court or government agency).</li>
                    </ul>
                </div>
            </div>

            <!-- 4. Data Security -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">04</span>
                    <h2 class="section-title">Data Security</h2>
                </div>
                <div class="policy-content">
                    <p>Tendapoa takes reasonable measures to help protect information about you from loss, theft, misuse, and unauthorized access, disclosure, alteration, and destruction.</p>
                    <p>However, no internet or electronic storage system is 100% secure. Therefore, we cannot guarantee the absolute security of your personal data.</p>
                </div>
            </div>

            <!-- 5. Your Rights and Choices -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">05</span>
                    <h2 class="section-title">Your Rights and Choices</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li><strong>Account Information:</strong> You may update or correct your account information at any time by logging into your account settings.</li>
                        <li><strong>Account Deletion:</strong> You have the right to request the deletion of your account and personal data, subject to any outstanding payments or legal obligations.</li>
                        <li><strong>Notifications:</strong> You may opt-out of receiving promotional communications from us by following the instructions in those communications. You will still receive non-promotional messages, such as those about your account or ongoing services.</li>
                    </ul>
                </div>
            </div>

            <!-- 6. Children's Privacy -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">06</span>
                    <h2 class="section-title">Children's Privacy</h2>
                </div>
                <div class="policy-content">
                    <p>Our Platform is intended for use by individuals who are at least 18 years old. We do not knowingly collect personal information from children under 18. If we become aware that we have collected personal data from a child under 18, we will take steps to delete that information.</p>
                </div>
            </div>

            <!-- 7. Changes to this Privacy Policy -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">07</span>
                    <h2 class="section-title">Changes to this Privacy Policy</h2>
                </div>
                <div class="policy-content">
                    <p>Tendapoa may update this Privacy Policy from time to time.</p>
                    <p>If we make significant changes, we will notify you by revising the date at the top of the policy and, depending on the specific changes, we may provide you with additional notice (such as adding a statement to our website's homepage or sending you a notification).</p>
                    <p>We encourage you to review the Privacy Policy whenever you access the Platform to stay informed about our information practices.</p>
                </div>
            </div>

            <!-- 8. Contact Information -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">08</span>
                    <h2 class="section-title">Contact Information</h2>
                </div>
                <div class="policy-content">
                    <p>If you have any questions or concerns about this Privacy Policy or our data practices, please contact us at:</p>
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
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.875rem;">Hakimiliki © <?php echo e(date('Y')); ?> Tendapoa.
                    Haki zote zimehifadhiwa.</p>
            </div>
            <div>
                <h3>Navigation</h3>
                <div class="footer-links">
                    <a href="/#home">Nyumbani</a>
                    <a href="/#services">Huduma</a>
                    <a href="/#about">Kuhusu</a>
                    <a href="<?php echo e(route('policy.fees-payments')); ?>">Sera ya Malipo & Ada</a>
                    <a href="<?php echo e(route('policy.terms')); ?>">Terms & Conditions</a>
                    <a href="<?php echo e(route('policy.privacy')); ?>" style="color: white; font-weight: 600;">Privacy Policy</a>
                    <a href="<?php echo e(route('register')); ?>">Pata Huduma</a>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
<?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/policy/privacy.blade.php ENDPATH**/ ?>