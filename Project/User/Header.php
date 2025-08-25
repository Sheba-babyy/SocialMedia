<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexo Navigation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;500;600;700&display=swap');
        
        :root {
            --dark-bg: #0A0A0A;
            --card-bg: rgba(255, 255, 255, 0.08);
            --accent-red: #E53935;
            --text-light: #F8F8F8;
            --text-subtle: #AFAFAF;
            --border-color: rgba(255, 255, 255, 0.15);
            --hover-bg: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            margin: 0;
            padding: 0;
            padding-top: 70px; /* Space for fixed nav */
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: var(--card-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-red);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .nav-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

       .nav-links a {
    position: relative;
    color: var(--text-light);
    text-decoration: none;
    padding: 8px 8px;
    border-radius: 50%; /* make background circular */
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;  /* fixed size for circle */
    height: 45px;
    background-color: #1a1a1a; /* dark circle like screenshot */
}

.nav-links a i {
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

/* Tooltip text */
.nav-links a span {
    position: absolute;
    bottom: -35px; /* show tooltip below */
    left: 50%;
    transform: translateX(-50%);
    background-color: #2c2c2c;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Tooltip arrow */
.nav-links a span::after {
    content: '';
    position: absolute;
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-bottom: 6px solid #2c2c2c;
}

/* Hover states */
.nav-links a:hover {
    background-color: var(--hover-bg);
}

.nav-links a:hover i {
    color: var(--accent-red);
    transform: scale(1.1);
}

.nav-links a:hover span {
    opacity: 1;
    visibility: visible;
}


        /* Active state */
        .nav-links a.active {
            background-color: rgba(229, 57, 53, 0.15);
        }

        .nav-links a.active i {
            color: var(--accent-red);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .header-container {
                padding: 12px 20px;
            }
            
            .logo {
                font-size: 1.7rem;
            }
            
            .nav-links {
                gap: 5px;
            }
            
            .nav-links a {
                padding: 8px 8px;
            }
            
            .nav-links a i {
                font-size: 1.5rem;
            }
            
            .nav-links a span {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                width: 100%;
                justify-content: space-around;
            }
            
            .nav-links a {
                padding: 10px;
                flex: 1;
                text-align: center;
            }
            
            body {
                padding-top: 100px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="HomePage.php" class="logo">Nexo</a>

            <nav class="nav-links">
                <a href="HomePage.php"><i class="fas fa-home"></i><span>Home</span></a>
                <a href="UserSearch.php"><i class="fas fa-search"></i><span>Search</span></a>
                <a href="FriendsList.php"><i class="fas fa-user-friends"></i><span>Friends</span></a>
                <a href="ChatList.php"><i class="fas fa-comment-dots"></i><span>Chats</span></a>
                <a href="Groups.php"><i class="fas fa-users"></i><span>Groups</span></a>
                <a href="MyProfile.php"><i class="fas fa-user"></i><span>My Profile</span></a>
                <a href="../Guest/Logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </div>
    </header>

    <script>
        // Add active class to current page link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>