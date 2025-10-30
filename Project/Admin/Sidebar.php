<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Body */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        /* For Chrome, Safari and Opera */
::-webkit-scrollbar {
  display: none;
}

/* For all browsers */
body {
  overflow: -moz-scrollbars-none; /* For Firefox (older versions) */
  -ms-overflow-style: none;       /* For Internet Explorer and Edge */
  scrollbar-width: none;          /* For Firefox (new versions) */
}

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2c3e50 0%, #1a2530 100%);
            color: white;
            height: 100vh;
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        /* Sidebar header with logo */
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 15px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            margin-bottom: 5px;
        }

        .logo-icon {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Sidebar links */
        .sidebar ul {
            list-style: none;
            padding: 0 15px;
            margin: 0;
        }

        .sidebar ul li {
            margin: 8px 0;
            position: relative;
        }

        .sidebar ul li a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 15px;
        }

        .sidebar ul li a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }

        .sidebar ul li a.active {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .sidebar ul li a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: #fff;
            border-radius: 0 4px 4px 0;
        }

        /* Logout button */
        .sidebar ul li:last-child a {
            color: #e74c3c;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
        }

        .sidebar ul li:last-child a:hover {
            background: rgba(231, 76, 60, 0.1);
        }

        /* Main content */
        .main-content {
            margin-left: 300px;
            padding: 30px;
            width: calc(100% - 300px);
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Dashboard widgets */
        .dashboard-widgets {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
        }

        .widget {
            background: white;
            padding: 25px;
            flex: 1;
            min-width: 250px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .widget:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .widget h3 {
            margin: 15px 0;
            font-size: 28px;
            color: #2c3e50;
        }

        .widget p {
            color: #7f8c8d;
            font-size: 16px;
            font-weight: 500;
        }

        /* Responsive: on smaller screens */
        @media (max-width: 992px) {
            .sidebar {
                width: 230px;
            }
            
            .main-content {
                margin-left: 240px;
                width: calc(100% - 240px);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: visible;
            }
            
            .sidebar .logo-text,
            .sidebar,
            .sidebar ul li a span {
                display: none;
            }
            
            .sidebar-header {
                padding: 20px 10px;
            }
            
            .sidebar ul li a i {
                margin-right: 0;
                font-size: 20px;
            }
            
            .sidebar ul {
                padding: 0 10px;
            }
            
            .sidebar ul li a {
                padding: 15px;
                justify-content: center;
            }
            
            .main-content {
                margin-left: 80px;
                width: calc(100% - 80px);
                padding: 20px;
            }
            
            .dashboard-widgets {
                gap: 15px;
            }
            
            .widget {
                min-width: calc(50% - 15px);
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .widget {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="#" class="logo">
                <div class="logo-icon">N</div>
                <span class="logo-text">Nexo</span>
            </a>     
        </div>
        <ul>
    <li><a href="HomePage.php" class="menu-item"><i class="fas fa-home"></i><span class="menu-text">Dashboard</span></a></li>
    <li><a href="AdminReg.php" class="menu-item"><i class="fas fa-user-plus"></i><span class="menu-text">Admin Registration</span></a></li>
    <li><a href="District.php" class="menu-item"><i class="fas fa-map"></i><span class="menu-text">District</span></a></li>
    <li><a href="Place.php" class="menu-item"><i class="fas fa-location-dot"></i><span class="menu-text">Place</span></a></li>
    <li><a href="Feedback.php" class="menu-item"><i class="fas fa-comment"></i><span class="menu-text">Feedback</span></a></li>
    <li><a href="Reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span class="menu-text">Reports</span></a></li>
    <li><a href="ViewComplaint.php" class="menu-item"><i class="fas fa-exclamation-triangle"></i><span class="menu-text">Complaints</span></a></li>
    <li><a href="Userlist.php" class="menu-item"><i class="fas fa-users"></i><span class="menu-text">Users</span></a></li>
    <li>
  <a href="ManageGroups.php" class="menu-item">
    <i class="fas fa-people-group"></i>
    <span class="menu-text">Manage Groups</span>
  </a>
</li>
    <li><a href="../Guest/Logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Logout</span></a></li>
    </ul>
    </div>
</body>
</html>