<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=Space+Grotesk:400,500,600,700" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #FFD700;
            --secondary: #FFA500;
            --dark: #1a1a1a;
            --card-bg: rgba(255, 255, 255, 0.03);
            --text: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            background: var(--dark);
            color: var(--text);
            font-family: 'Space Grotesk', sans-serif;
            overflow-x: hidden;
            scrollbar-width: none; /* Firefox */
        }

        /* Hide scrollbar for Chrome/Safari */
        body::-webkit-scrollbar {
            display: none;
        }

        .navbar {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(20px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 1.5rem 0;
        }

        .navbar.scrolled {
            padding: 1rem 0;
            background: rgba(26, 26, 26, 0.95);
        }

        .navbar-brand, .nav-link {
            color: var(--primary) !important;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar-brand:hover, .nav-link:hover {
            color: var(--secondary) !important;
            transform: translateY(-2px) scale(1.05);
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: radial-gradient(circle at center, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
        }

        .gradient-text {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
            position: relative;
        }

        .hero h1 {
            font-size: 5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            animation: slideUp 1s cubic-bezier(0.4, 0, 0.2, 1);
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2.5rem;
            animation: slideUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.2s backwards;
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: var(--dark);
            padding: 1.2rem 3rem;
            border-radius: 100px;
            border: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--secondary), var(--primary));
            z-index: -1;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scaleX(0);
            transform-origin: right;
        }

        .btn-custom:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }

        .btn-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.2);
            color: var(--dark);
        }

        .feature {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 3rem;
            margin: 1.5rem 0;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 215, 0, 0.1);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255, 215, 0, 0.1), transparent);
            transform: translateY(100%);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature:hover::before {
            transform: translateY(0);
        }

        .feature:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 60px rgba(255, 215, 0, 0.1);
        }

        .feature h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .feature p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        #features {
            padding: 8rem 0;
            position: relative;
        }

        #features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
            filter: blur(50px);
        }

        footer {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(20px);
            padding: 3rem 0;
            margin-top: 4rem;
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .row > div {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .row > div.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .parallax {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

   

        .btn-custom {
            background-color: #feca57;
            color: #343a40;
            padding: 10px 30px;
            font-size: 1.2rem;
            border-radius: 25px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #ffbe30;
            transform: translateY(-3px);
        }

        /* Preloader Effect */
        .preloader {
            position: relative;
            overflow: hidden;
        }

        .preloader::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: preloader 1.5s infinite;
        }

        @keyframes preloader {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md fixed-top">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    Dev.{{ config('app.name', 'Laravel') }}
                </a>
        
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
        
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Right Side of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/documentation') }}">Documentation</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        

        <header class="hero">
            <div class="text-center hero-content">
                <h1>Experience <span class="gradient-text">Next-Level</span><br>Booking</h1>
                <p>Elevate your journey with premium rentals</p>
                <a href="#features" class="btn btn-custom preloader">Running...</a>
            </div>
        </header>


        <main>
            <div class="container" id="features">
                <div class="row">
                    <div class="col-md-4">
                        <div class="feature parallax">
                            <h3>Seamless Booking</h3>
                            <p>Experience lightning-fast bookings with our intuitive interface. Smart recommendations and instant confirmations make planning effortless.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature parallax">
                            <h3>Curated Selection</h3>
                            <p>Access our handpicked collection of premium properties and experiences, vetted for quality and uniqueness.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature parallax">
                            <h3>Concierge Service</h3>
                            <p>Enjoy round-the-clock premium support from our dedicated team of travel experts and local insiders.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <div class="container text-center">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Intersection Observer for fade-in effects
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.row > div').forEach((el) => observer.observe(el));

        // Parallax effect for features
        document.addEventListener('mousemove', (e) => {
            const parallaxElements = document.querySelectorAll('.parallax');
            parallaxElements.forEach((el) => {
                const speed = 0.05;
                const x = (window.innerWidth - e.pageX * speed) / 100;
                const y = (window.innerHeight - e.pageY * speed) / 100;
                el.style.transform = `translateX(${x}px) translateY(${y}px)`;
            });
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
