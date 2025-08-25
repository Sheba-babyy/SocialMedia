<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexo - Connect with Your Community</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;500;600;700&display=swap');
        
        :root {
            --dark-bg: #0A0A0A; /* Deeper, richer black */
            --card-bg: rgba(255, 255, 255, 0.08); /* More transparent for better blur */
            --accent-red: #E53935; /* Brighter, more vibrant red */
            --text-light: #F8F8F8;
            --text-subtle: #AFAFAF;
            --border-color: rgba(255, 255, 255, 0.15);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            overflow-x: hidden;
        }

        .header {
            padding: 20px 0;
            position: relative;
            z-index: 10;
        }

        .header .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-red);
        }

        .nav-link {
            color: var(--text-light);
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .nav-link:hover {
            color: var(--accent-red);
        }
        
        .nav-link.active::after {
            content: '';
            display: block;
            width: 100%;
            height: 2px;
            background-color: var(--accent-red);
            margin-top: 5px;
            transform-origin: left;
            animation: expand-underline 0.3s forwards;
        }

        @keyframes expand-underline {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        .hero-section {
            padding: 100px 0;
            position: relative;
        }

        .hero-image {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            animation: float 4s ease-in-out infinite alternate; /* Continuous floating animation */
        }

        @keyframes float {
            from { transform: translateY(0px); }
            to { transform: translateY(-20px); }
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--text-subtle);
            margin-bottom: 30px;
        }

        .hero-description {
            font-size: 1rem;
            color: var(--text-subtle);
            line-height: 1.6;
        }

        .divider {
            height: 1px;
            background-color: var(--border-color);
            margin: 80px 0;
        }
        
        .glass-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(15px); /* Stronger blur effect */
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 1s ease-out, transform 1s ease-out;
            will-change: opacity, transform;
            height: 100%; /* Ensure cards are same height */
        }

        .glass-card.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-red);
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease, transform 0.8s ease;
            will-change: opacity, transform;
        }
        
        .profile-img.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .feature-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }
        
        .btn-nexo {
            background-color: var(--accent-red);
            border: none;
            color: var(--text-light);
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-nexo:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(229, 57, 53, 0.4);
        }

        .cta-section {
            background-color: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 60px;
            text-align: center;
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 1s ease-out, transform 1s ease-out;
            will-change: opacity, transform;
        }

        .cta-section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .cta-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .cta-text {
            color: var(--text-subtle);
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        footer {
            padding: 40px 0;
            color: var(--text-subtle);
            font-size: 0.9rem;
            text-align: center;
            border-top: 1px solid var(--border-color);
            margin-top: 80px;
        }

    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 px-5">
            <!-- Header -->
            <header class="header d-flex justify-content-between align-items-center">
                <a class="logo text-decoration-none" href="#">Nexo</a>
                <nav class="d-none d-md-block">
                    <ul class="nav">
                        <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="Guest/NewUser.php">SignUp</a></li>
                        <li class="nav-item"><a class="nav-link" href="Guest/Login.php">Login</a></li>
                    </ul>
                </nav>
            </header>
        </div>
    </div>
</div>

<main class="container">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 animate-on-scroll">
                <!-- Pinterest-style image. The image is a placeholder and should be replaced. -->
                <img src="../Docs/img/social-media.jpg" 
                     class="img-fluid hero-image" alt="Social media on phone">
            </div>
            <div class="col-lg-6 text-center text-lg-start animate-on-scroll" style="animation-delay: 0.3s;">
                <h1 class="hero-title">Your Social Canvas.</h1>
                <h2 class="hero-subtitle">Connect, share, and inspire.</h2>
                <p class="hero-description">
                    Nexo provides a creative space for you to connect with communities that matter. 
                    Share your passions, discover new ideas, and build a network that truly reflects you.
                </p>
                <a href="Guest/Login.php" class="btn btn-nexo mt-3">Get Started</a>
            </div>
        </div>
    </section>

    <!-- Divider -->
    <div class="divider animate-on-scroll"></div>

    <!-- Features Section with Glassmorphism -->
    <section class="features-section">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-5 animate-on-scroll">
                <h2 class="hero-title" id="features">Our Community Values</h2>
                <p class="hero-subtitle">A platform built for meaningful connections.</p>
            </div>
        </div>
        <div class="row g-4 justify-content-center">
            <!-- Glass Card 1 -->
            <div class="col-md-4 col-sm-6 d-flex justify-content-center">
                <div class="glass-card text-center animate-on-scroll" style="animation-delay: 0.2s;">
                    <img src="https://i.pinimg.com/1200x/fa/00/2c/fa002c7cd3c67fee447b69f943d3a12a.jpg" 
                         class="profile-img animate-on-scroll" alt="Profile of a happy user" style="animation-delay: 0.5s;">
                    <h3 class="feature-title">Authentic Sharing</h3>
                    <p class="text-subtle">
                        Share your true self with a community that celebrates authenticity and genuine connection.
                    </p>
                </div>
            </div>
            <!-- Glass Card 2 -->
            <div class="col-md-4 col-sm-6 d-flex justify-content-center">
                <div class="glass-card text-center animate-on-scroll" style="animation-delay: 0.4s;">
                    <img src="https://i.pinimg.com/1200x/94/0f/48/940f48d59431ad967a5fb2018951ee73.jpg" 
                         class="profile-img animate-on-scroll" alt="Profile of a woman on a phone" style="animation-delay: 0.7s;">
                    <h3 class="feature-title">Creative Expression</h3>
                    <p class="text-subtle">
                        From photos to stories, Nexo gives you the tools to express your creativity freely and beautifully.
                    </p>
                </div>
            </div>
            <!-- Glass Card 3 -->
            <div class="col-md-4 col-sm-6 d-flex justify-content-center">
                <div class="glass-card text-center animate-on-scroll" style="animation-delay: 0.6s;">
                    <img src="https://i.pinimg.com/736x/08/b5/35/08b535df5ddc428274ce8661a9bd39aa.jpg" 
                         class="profile-img animate-on-scroll" alt="Profile of a person laughing" style="animation-delay: 0.9s;">
                    <h3 class="feature-title">Privacy First</h3>
                    <p class="text-subtle">
                        Your data and your privacy are our top priority. We give you full control over your digital footprint.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Divider -->
    <div class="divider animate-on-scroll"></div>

    <!-- Call to Action Section with Glassmorphism -->
    <section class="cta-section animate-on-scroll">
        <h2 class="cta-title">Ready to Join Nexo?</h2>
        <p class="cta-text">
            Start your journey today and become part of a community that truly understands you.
        </p>
        <a href="Guest/NewUser.php" class="btn btn-nexo btn-lg">Sign Up Now</a>
    </section>
</main>

<footer>
    &copy; 2024 Nexo. All Rights Reserved.
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const animatedElements = document.querySelectorAll('.animate-on-scroll');
        animatedElements.forEach(element => observer.observe(element));

        // Add visibility class to profile images after cards are visible
        const cardObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const profileImages = entry.target.querySelectorAll('.profile-img');
                    profileImages.forEach(img => {
                        img.classList.add('is-visible');
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        const glassCards = document.querySelectorAll('.glass-card');
        glassCards.forEach(card => cardObserver.observe(card));
    });
</script>

</body>
</html>
