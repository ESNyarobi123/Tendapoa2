<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Tendapoa - Usafi wa Kuegemea, Kazi za Kuegemea</title>
    <meta name="description" content="Pata usafi wa kuegemea na kazi za kuegemea kwa urahisi na salama. Tendapoa inaunganisha wafanyakazi na wateja kwa njia ya kisasa na salama.">
    
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
            background: #ffffff;
            overflow-x: hidden;
        }
        
        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .header.scrolled {
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
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
            font-size: 1.5rem;
            font-weight: 800;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            transition: color 0.3s ease;
            font-size: 0.95rem;
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
            background: #2563eb;
            color: white;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            color: #2563eb;
            border: 2px solid #2563eb;
        }
        
        .btn-outline:hover {
            background: #2563eb;
            color: white;
        }
        
        /* Hero Section */
        .hero {
            position: relative;
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8rem 2rem 4rem;
            overflow: hidden;
        }
        
        .hero-background {
            transition: transform 0.3s ease;
        }
        
        .hero:hover .hero-background {
            transform: scale(1.02);
        }
        
        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('{{ asset("hero.png") }}') center center/cover no-repeat;
            z-index: 0;
        }
        
        .hero-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.85) 0%, rgba(59, 130, 246, 0.75) 100%);
            z-index: 1;
        }
        
        .hero-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><radialGradient id="grad" cx="50%" cy="50%"><stop offset="0%" stop-color="rgba(255,255,255,0.1)"/><stop offset="100%" stop-color="rgba(255,255,255,0)"/></radialGradient></defs><circle cx="200" cy="200" r="150" fill="url(%23grad)"/><circle cx="900" cy="300" r="200" fill="url(%23grad)"/><circle cx="500" cy="600" r="180" fill="url(%23grad)"/></svg>');
            opacity: 0.2;
            animation: float 20s ease-in-out infinite;
            z-index: 1;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
            animation: fadeInUp 1s ease-out;
        }
        
        .hero h1 {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: #ffffff;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .hero p {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 2.5rem;
            line-height: 1.8;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Services Section */
        .services-section {
            padding: 6rem 2rem;
            background: #ffffff;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: #2563eb;
            border-radius: 2px;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .service-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        }
        
        .service-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }
        
        .service-description {
            color: #64748b;
            line-height: 1.7;
            font-size: 1rem;
        }
        
        /* Statistics Section */
        .stats-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
        }
        
        .stats-title {
            font-size: 2.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: white;
        }
        
        .stat-label {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        /* Areas Served Section */
        .areas-section {
            padding: 6rem 2rem;
            background: #f8fafc;
        }
        
        .areas-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        
        .areas-text h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        
        .areas-text p {
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 2rem;
            font-size: 1.125rem;
        }
        
        .areas-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        
        .area-item {
            color: #475569;
            font-weight: 500;
            padding: 0.5rem;
        }
        
        .map-container {
            background: #e2e8f0;
            border-radius: 16px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        /* Testimonials Section */
        .testimonials-section {
            padding: 6rem 2rem;
            background: #ffffff;
        }
        
        .testimonials-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .testimonial-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        }
        
        .testimonial-text {
            color: #475569;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
            font-style: italic;
        }
        
        .testimonial-author {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.125rem;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            text-align: center;
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: white;
            line-height: 1.3;
        }
        
        /* Footer */
        .footer {
            background: #1e293b;
            color: white;
            padding: 4rem 2rem 2rem;
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
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #4a5568;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero {
                min-height: 70vh;
                padding: 6rem 1rem 3rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .services-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            
            .areas-content {
                grid-template-columns: 1fr;
            }
            
            .map-container {
                height: 300px;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="nav-container">
            <div class="logo">
                🧹 Tendapoa
            </div>
            <nav class="nav-links" id="navLinks">
                <a href="#home">Nyumbani</a>
                <a href="#services">Huduma</a>
                <a href="#about">Kuhusu</a>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline">Ingia</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary">Pata Huduma</a>
                        @endif
                    @endauth
                @endif
            </nav>
            <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-background"></div>
        <div class="hero-content fade-in-up">
            <h1>Tendapoa</h1>
            <p>Tendapoa inatoa suluhisho bora za usafi, kubadilisha nafasi kuwa maeneo safi kupitia uangalifu wa pekee na kujitolea kwa ubora.</p>
            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn btn-primary">
                    Pata Huduma
                </a>
            </div>
        </div>
    </section>

    <!-- Recurring Services Section -->
    <section class="services-section" id="services">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Huduma za Usafi za Kawaida</h2>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <span class="service-icon">⏰</span>
                    <h3 class="service-title">Huduma za Usafi za Kila Wiki</h3>
                    <p class="service-description">Huduma za usafi za kila wiki zinatolea uongozi thabiti na wa kuaminika. Wafanyakazi wetu wana ujuzi wa kutosha kuhakikisha nafasi yako inabaki safi na ya kuvutia kila wakati.</p>
                </div>
                <div class="service-card">
                    <span class="service-icon">🔔</span>
                    <h3 class="service-title">Huduma za Usafi za Kila Wiki Mbili</h3>
                    <p class="service-description">Huduma za usafi za kila wiki mbili hukupa usawa bora kati ya matengenezo na ufanisi wa gharama. Hii ni chaguo bora kwa wale ambao wanahitaji usafi wa mara kwa mara lakini si kila wiki.</p>
                </div>
                <div class="service-card">
                    <span class="service-icon">✅</span>
                    <h3 class="service-title">Huduma za Usafi za Kila Mwezi</h3>
                    <p class="service-description">Huduma za usafi za kila mwezi hukupa usafi wa mara kwa mara, kusafisha, kufuta vumbi, kuvuta vumbi, na kazi muhimu za matengenezo ili kuhakikisha nafasi yako inabaki katika hali nzuri.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- One Time Services Section -->
    <section class="services-section" style="background: #f8fafc;">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Huduma za Usafi za Mara Moja</h2>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <span class="service-icon">➕</span>
                    <h3 class="service-title">Usafi wa Kuhamia na Kuingia</h3>
                    <p class="service-description">Usafi wa kina kwa ajili ya mali za zamani au mpya. Tunahakikisha kila kona inasafishwa kwa uangalifu kabla ya kuhamia au baada ya kuhamia.</p>
                </div>
                <div class="service-card">
                    <span class="service-icon">✨</span>
                    <h3 class="service-title">Usafi wa Kina na wa Masika</h3>
                    <p class="service-description">Usafi wa kina na wa masika unaobadilisha nafasi yako. Huduma hii ni ya kina na inaangalia kila kitu kwa uangalifu ili kuhakikisha nafasi yako inakuwa safi kabisa.</p>
                </div>
                <div class="service-card">
                    <span class="service-icon">⚙️</span>
                    <h3 class="service-title">Usafi baada ya Ujenzi</h3>
                    <p class="service-description">Kuondoa mabaki ya ujenzi, vumbi, na mabaki baada ya ujenzi. Huduma hii inahakikisha nafasi yako inarudi katika hali yake ya awali baada ya ujenzi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <h2 class="stats-title">Okoa Muda Wako Na Pumzika</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Kazi Zilizokamilika</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">200+</div>
                    <div class="stat-label">Wateja Kwa Mwaka</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">5+</div>
                    <div class="stat-label">Miaka ya Uzoefu</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Wateja Wameridhika</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Areas Served Section -->
    <section class="areas-section" id="about">
        <div class="container">
            <div class="areas-content">
                <div class="areas-text">
                    <h2>Maeneo Tunayohudumia</h2>
                    <p>Tendapoa inahudumia maeneo mengi katika Tanzania, ikiwa na kujitolea kwa ubora. Tunatoa huduma za usafi za kuegemea katika maeneo yafuatayo na mengineyo:</p>
                    <div class="areas-list">
                        <div class="area-item">Dar es Salaam</div>
                        <div class="area-item">Arusha</div>
                        <div class="area-item">Mwanza</div>
                        <div class="area-item">Dodoma</div>
                        <div class="area-item">Tanga</div>
                        <div class="area-item">Morogoro</div>
                        <div class="area-item">Mbeya</div>
                        <div class="area-item">Zanzibar</div>
                        <div class="area-item">Kilimanjaro</div>
                        <div class="area-item">Iringa</div>
                        <div class="area-item">Tabora</div>
                        <div class="area-item">Mtwara</div>
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        Pata Huduma
                    </a>
                </div>
                <div class="map-container">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🗺️</div>
                        <div>Ramani ya Maeneo Tunayohudumia</div>
                        <div style="font-size: 0.875rem; margin-top: 0.5rem; color: #94a3b8;">Tanzania - Maeneo Yote</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Maoni ya Wateja</h2>
            </div>
            <div class="testimonials-container">
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <p class="testimonial-text">"Tendapoa imenibadilisha nyumbani kwangu kuwa nafasi safi na ya kuvutia. Huduma zao ni bora na wafanyakazi wao ni waaminifu na wenye ujuzi."</p>
                        <div class="testimonial-author">— Juma Hassan</div>
                    </div>
                    <div class="testimonial-card">
                        <p class="testimonial-text">"Tendapoa imezidi matarajio yangu. Wamefanya kazi kubwa na nafasi yangu imekuwa safi na ya kuvutia zaidi kuliko nilivyokuwa natarajia."</p>
                        <div class="testimonial-author">— Amina Juma</div>
                    </div>
                    <div class="testimonial-card">
                        <p class="testimonial-text">"Asante Tendapoa kwa kazi yenu ya ajabu. Nafasi yangu imekuwa safi na ya kuvutia, na sasa ninaweza kupumzika na kufurahia nafasi yangu."</p>
                        <div class="testimonial-author">— Fatuma Ali</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Kufikia Kuridhika kwa Wateja bila Kukosa: Ahadi Yangu kama Huduma ya Usafi ya Kitaalamu</h2>
                <a href="{{ route('register') }}" class="btn btn-primary" style="background: white; color: #2563eb; font-size: 1rem; padding: 1rem 2rem;">
                    Pata Huduma
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div>
                <div class="footer-logo">🧹 Tendapoa</div>
                <p class="footer-description">Tendapoa inatoa suluhisho bora za usafi, kubadilisha nafasi kuwa maeneo safi kupitia uangalifu wa pekee na kujitolea kwa ubora.</p>
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.875rem;">Hakimiliki © {{ date('Y') }} Tendapoa. Haki zote zimehifadhiwa.</p>
            </div>
            <div>
                <h3>Navigation</h3>
                <div class="footer-links">
                    <a href="#home">Nyumbani</a>
                    <a href="#services">Huduma</a>
                    <a href="#about">Kuhusu</a>
                    <a href="{{ route('register') }}">Pata Huduma</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const navLinks = document.getElementById('navLinks');
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe service cards and testimonials
        document.querySelectorAll('.service-card, .testimonial-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        // Stats counter animation
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start) + '+';
                }
            }, 16);
        }

        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        const text = stat.textContent;
                        const number = parseInt(text.replace(/\D/g, ''));
                        if (number && !isNaN(number)) {
                            stat.textContent = '0';
                            animateCounter(stat, number);
                        }
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }
    </script>
</body>
</html>
