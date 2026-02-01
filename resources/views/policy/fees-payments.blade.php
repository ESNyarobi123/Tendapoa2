<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sera ya Malipo na Ada - Tendapoa</title>

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
                /* Simplify for mobile for now or copy script */
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
            <h1 class="page-title">Sera ya Malipo, Ada na<br>Kukamilisha Huduma</h1>
            <p class="page-subtitle">Mwongozo kamili wa jinsi Tendapoa inavyofanya kazi kulinda maslahi yako</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-container">
        <div class="policy-card">

            <!-- 1. Platform Role -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">01</span>
                    <h2 class="section-title">Nafasi ya Mfumo (Platform Role)</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Tendapoa inafanya kazi kama soko la kidijitali linalounganisha wateja na watoa huduma
                            binafsi.</li>
                        <li>Tendapoa haitoi huduma za usafi au dobi moja kwa moja.</li>
                        <li>Tendapoa inawezesha uchapishaji wa kazi, kuhifadhi malipo, kuunganisha watoa huduma, na
                            malipo.</li>
                    </ul>
                </div>
            </div>

            <!-- 2. Customer Upfront Payment -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">02</span>
                    <h2 class="section-title">Malipo ya Awali ya Mteja (Budget-Based)</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Kabla ya kuchapisha kazi, wateja wanatakiwa kulipa kiasi cha bajeti yao iliyotajwa mapema.
                        </li>
                        <li>Mteja huweka bajeti ya huduma wakati wa kuchapisha kazi.</li>
                        <li>Kazi itachapishwa na kuonekana kwa watoa huduma tu baada ya malipo hayo kufanikiwa.</li>
                        <li>Malipo ya awali yanaonyesha kujitolea na kuhakikisha ombi la huduma.</li>
                    </ul>
                </div>
            </div>

            <!-- 3. Escrow Holding -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">03</span>
                    <h2 class="section-title">Kuhifadhi Malipo ya Mteja (Escrow)</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Malipo yote ya mteja yanahifadhiwa kwa usalama na Tendapoa hadi huduma itakapokamilika.</li>
                        <li>Fedha hazitolewi kwa mtoa huduma hadi kukamilika kwa kazi kudhibitishwe.</li>
                        <li>Hii inalinda wateja na watoa huduma dhidi ya udanganyifu au kutokutekelezwa kwa kazi.</li>
                    </ul>
                </div>
            </div>

            <!-- 4. Budget vs Final Price -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">04</span>
                    <h2 class="section-title">Bajeti dhidi ya Bei ya Mwisho ya Huduma</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Malipo ya awali yanawakilisha kiasi cha bajeti, siyo lazima iwe bei ya mwisho.</li>
                        <li>Ikiwa bei ya mwisho iliyokubaliwa iko ndani ya bajeti, malipo yanaendelea kama kawaida.</li>
                        <li>Ikiwa bei ya mwisho inazidi bajeti, mteja lazima aidhinishe na kulipa kiasi cha ziada kabla
                            ya kukamilika.</li>
                        <li>Ikiwa bei ya mwisho ni ndogo kuliko bajeti, kiasi kilichokubaliwa tu kitatolewa, na salio
                            lolote linaweza kurudishwa au kushughulikiwa kulingana na sheria za mfumo.</li>
                    </ul>
                </div>
            </div>

            <!-- 5. Completion Code -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">05</span>
                    <h2 class="section-title">Uthibitisho wa Kukamilika (Completion Code)</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Baada ya kukubali kazi, Tendapoa inazalisha namba maalum ya kukamilisha (completion code)
                            kwa ajili ya huduma hiyo.</li>
                        <li>Namba hiyo inatolewa kwa mteja kupitia programu (app).</li>
                        <li>Mteja lazima ampe mtoa huduma namba hiyo tu baada ya kuridhika na huduma iliyotolewa.</li>
                        <li>Mtoa huduma lazima aingize namba sahihi ya kukamilisha kwenye mfumo.</li>
                    </ul>
                </div>
            </div>

            <!-- 6. Release of Funds -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">06</span>
                    <h2 class="section-title">Kuachilia Fedha kwa Mtoa Huduma</h2>
                </div>
                <div class="policy-content">
                    <p>Mara baada ya namba sahihi ya kukamilisha kuwasilishwa:</p>
                    <ul>
                        <li>Kazi inawekwa alama kuwa imekamilika.</li>
                        <li>Kiasi kinacholipwa (chini ya kamisheni inayotumika) kinawekwa kwenye pochi ya TendaPoa ya
                            mtoa huduma.</li>
                        <li>Hakuna fedha zitakazotolewa bila namba sahihi ya kukamilisha isipokuwa zitatuliwe kupitia
                            mchakato wa migogoro.</li>
                    </ul>
                </div>
            </div>

            <!-- 7. Provider Commission -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">07</span>
                    <h2 class="section-title">Kamisheni ya Mtoa Huduma</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Tendapoa inatoza kamisheni ya 10% kwa jumla ya thamani ya kila huduma iliyokamilika kwa
                            mafanikio.</li>
                        <li>Kamisheni inakatwa moja kwa moja wakati wa kukamilisha kazi.</li>
                        <li>Kiasi kinachobaki kinawekwa kwenye pochi ya mtoa huduma.</li>
                        <li>Kamisheni inatumika tu kwa huduma zilizohifadhiwa na kukamilika kupitia Tendapoa.</li>
                    </ul>
                </div>
            </div>

            <!-- 8. Provider Wallet -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">08</span>
                    <h2 class="section-title">Pochi ya Mtoa Huduma (Wallet)</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Watoa huduma wanapokea mapato yao kwenye Pochi yao ya Tendapoa.</li>
                        <li>Salio la pochi linaonyesha mapato halisi baada ya kamisheni.</li>
                        <li>Fedha zinabaki kwenye pochi hadi uondoaji uanzishwe.</li>
                    </ul>
                </div>
            </div>

            <!-- 9. Withdrawals -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">09</span>
                    <h2 class="section-title">Uondoaji wa Fedha (Withdrawals)</h2>
                </div>
                <div class="policy-content">
                    <p>Watoa huduma wanaweza kutoa fedha kulingana na sheria zifuatazo:</p>
                    <ul>
                        <li>Kiasi cha chini cha kutoa: TZS 5,000</li>
                        <li>Ada ya kutoa: TZS 500 kwa kila muamala (ada ya kudumu)</li>
                        <li>Ada za kutoa zinakatwa moja kwa moja wakati wa kutoa.</li>
                        <li>Uondoaji chini ya kiasi cha chini hauruhusiwi.</li>
                    </ul>
                </div>
            </div>

            <!-- 10. Payment Processing -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">10</span>
                    <h2 class="section-title">Njia za Malipo na Uchakataji</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Malipo na uondoaji huchakatwa kupitia washirika wa pesa za simu au benki wanaotumika.</li>
                        <li>Muda wa kuchakata unaweza kutofautiana kulingana na watoa huduma wengine.</li>
                        <li>Tendapoa haiwajibiki kwa ucheleweshaji unaosababishwa na mifumo ya malipo ya nje.</li>
                    </ul>
                </div>
            </div>

            <!-- 11. Failed Withdrawals -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">11</span>
                    <h2 class="section-title">Uondoaji Uliofeli</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Ikiwa uondoaji utafeli kutokana na maelezo yasiyo sahihi au makosa ya mfumo:</li>
                        <li>Fedha zitarudishwa kwenye pochi ya mtoa huduma baada ya uthibitisho.</li>
                        <li>Ada za kutoa zinaweza bado kutumika kulingana na sheria za mtoa huduma wa malipo.</li>
                    </ul>
                </div>
            </div>

            <!-- 12. Customer Responsibilities -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">12</span>
                    <h2 class="section-title">Wajibu wa Mteja</h2>
                </div>
                <div class="policy-content">
                    <p>Wateja wanawajibika kwa:</p>
                    <ul>
                        <li>Kutoa maelezo sahihi ya kazi na bajeti.</li>
                        <li>Kutunza namba ya kukamilisha (completion code).</li>
                        <li>Kutoa namba ya kukamilisha tu baada ya kuridhika na huduma.</li>
                        <li>Kushiriki namba ya kukamilisha kabla ya huduma kukamilika ni kwa hatari ya mteja mwenyewe.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 13. Disputes -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">13</span>
                    <h2 class="section-title">Migogoro na Vighairi</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Katika hali ya migogoro au kushindwa kutoa namba ya kukamilisha:</li>
                        <li>Tendapoa inaweza kushikilia fedha kwa muda.</li>
                        <li>Pande zote mbili zinaweza kuhitajika kuwasilisha ushahidi.</li>
                        <li>Tendapoa inaweza kukamilisha kazi kikamilifu na kutoa fedha pale inapothibitishwa.</li>
                    </ul>
                </div>
            </div>

            <!-- 14. Transparency -->
            <div class="policy-section">
                <div class="section-header">
                    <span class="section-number">14</span>
                    <h2 class="section-title">Uwazi na Mabadiliko ya Ada</h2>
                </div>
                <div class="policy-content">
                    <ul>
                        <li>Tendapoa imejitolea kwa bei za uwazi.</li>
                        <li>Ada zote zinazotumika zinaonyeshwa kabla ya uthibitisho.</li>
                        <li>Tendapoa inahifadhi haki ya kurekebisha ada kwa kutoa taarifa mapema.</li>
                        <li>Kuendelea kutumia mfumo kunamaanisha kukubali masharti yaliyosasishwa.</li>
                    </ul>
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
                    <a href="{{ route('policy.fees-payments') }}" style="color: white; font-weight: 600;">Sera ya Malipo
                        & Ada</a>
                    <a href="{{ route('register') }}">Pata Huduma</a>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>