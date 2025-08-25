<?php
include '../Assets/Connection/Connection.php';

// --- Get stats ---
$userCount = $con->query("SELECT COUNT(*) AS c FROM tbl_user")->fetch_assoc()['c'];
$postCount = $con->query("SELECT COUNT(*) AS c FROM tbl_post")->fetch_assoc()['c'];
$feedbackCount = $con->query("SELECT COUNT(*) AS c FROM tbl_feedback")->fetch_assoc()['c'];
$complaintCount = $con->query("SELECT COUNT(*) AS c FROM tbl_complaint")->fetch_assoc()['c'];

// --- Complaints status ---
$complaintsResolved = $con->query("SELECT COUNT(*) AS c FROM tbl_complaint WHERE complaint_status=1")->fetch_assoc()['c'];
$complaintsPending = $con->query("SELECT COUNT(*) AS c FROM tbl_complaint WHERE complaint_status=0")->fetch_assoc()['c'];
$complaintsReviewed = $con->query("SELECT COUNT(*) AS c FROM tbl_complaint WHERE complaint_status=2")->fetch_assoc()['c'];

// --- Complaints per month ---
$complaintsMonths = [];
$complaintsData = [];
$res1 = $con->query("SELECT DATE_FORMAT(complaint_date, '%Y-%m') AS m, COUNT(*) AS c 
                     FROM tbl_complaint 
                     GROUP BY m ORDER BY m DESC LIMIT 6");
while($row = $res1->fetch_assoc()){
    $complaintsMonths[] = $row['m'];
    $complaintsData[] = $row['c'];
}
$complaintsMonths = array_reverse($complaintsMonths);
$complaintsData = array_reverse($complaintsData);

// --- Feedback per month ---
$feedbackData = [];
$res2 = $con->query("SELECT DATE_FORMAT(feedback_date, '%Y-%m') AS m, COUNT(*) AS c 
                     FROM tbl_feedback 
                     GROUP BY m ORDER BY m DESC LIMIT 6");
$tmp = [];
while($row = $res2->fetch_assoc()){
    $tmp[$row['m']] = $row['c'];
}
foreach($complaintsMonths as $m){
    $feedbackData[] = $tmp[$m] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Use the Inter font from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Boxicons for icons -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Load ApexCharts for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            @apply bg-gray-100 text-gray-800;
        }

        /* Set scrollbar style for a cleaner look */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #e2e8f0;
        }
        ::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }
        
        .main-content {
            flex: 1;
            padding: 1.5rem 2rem;
            overflow-y: auto;
        }
    </style>
</head>
<body class="flex min-h-screen">
<?php include 'Sidebar.php'?>
    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Header -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 tracking-tight">Dashboard Overview</h1>
                <p class="text-lg text-gray-500 mt-2">A comprehensive look at your platform's statistics.</p>
            </div>
            <div class="mt-4 sm:mt-0 bg-white shadow-sm rounded-xl py-3 px-6 text-gray-600 font-medium flex items-center space-x-2">
                <i class="bx bx-calendar text-indigo-500 text-xl"></i>
                <span id="current-date"></span>
            </div>
        </header>

        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-lg transition-all transform hover:scale-105 hover:shadow-xl border-l-4 border-indigo-500">
                <div class="flex flex-col items-center">
                    <div class="bg-indigo-100 text-indigo-600 p-4 rounded-full mb-3">
                        <i class="bx bx-user text-3xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Total Users</p>
                    <h2 class="text-4xl font-bold text-gray-900 mt-1"><?= $userCount ?></h2>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg transition-all transform hover:scale-105 hover:shadow-xl border-l-4 border-blue-500">
                <div class="flex flex-col items-center">
                    <div class="bg-blue-100 text-blue-600 p-4 rounded-full mb-3">
                        <i class="bx bx-edit text-3xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Total Posts</p>
                    <h2 class="text-4xl font-bold text-gray-900 mt-1"><?= $postCount ?></h2>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg transition-all transform hover:scale-105 hover:shadow-xl border-l-4 border-green-500">
                <div class="flex flex-col items-center">
                    <div class="bg-green-100 text-green-600 p-4 rounded-full mb-3">
                        <i class="bx bx-message text-3xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Feedback Items</p>
                    <h2 class="text-4xl font-bold text-gray-900 mt-1"><?= $feedbackCount ?></h2>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg transition-all transform hover:scale-105 hover:shadow-xl border-l-4 border-red-500">
                <div class="flex flex-col items-center">
                    <div class="bg-red-100 text-red-600 p-4 rounded-full mb-3">
                        <i class="bx bx-error text-3xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Complaints</p>
                    <h2 class="text-4xl font-bold text-gray-900 mt-1"><?= $complaintCount ?></h2>
                </div>
            </div>
        </div>

        <!-- Charts and Latest Activity Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Doughnut Chart Card -->
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Complaint Status</h3>
                <div class="h-80 flex items-center justify-center">
                    <div id="complaintChart"></div>
                </div>
            </div>

            <!-- Line Chart Card -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Complaints vs Feedback (Last 6 Months)</h3>
                <div class="h-80">
                    <div id="reportTrend"></div>
                </div>
            </div>
        </div>

        <!-- Latest Registered Users Section -->
        <div class="bg-white p-6 rounded-2xl shadow-lg mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Latest Registered Users</h3>
            <ul class="divide-y divide-gray-200">
                <?php
                // User's original PHP code for latest users
                $latestUsers = $con->query("SELECT user_name FROM tbl_user ORDER BY user_id DESC LIMIT 5");
                while($u = $latestUsers->fetch_assoc()){
                    $initials = substr($u['user_name'], 0, 1);
                    echo "
                    <li class='py-4 flex items-center space-x-4'>
                        <div class='w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-900 font-bold'>$initials</div>
                        <span class='font-medium text-gray-900'>{$u['user_name']}</span>
                    </li>
                    ";
                }
                ?>
            </ul>
        </div>
    </div>

    <script>
        // Data and configuration for charts
        const complaintsResolved = <?= $complaintsResolved ?>;
        const complaintsPending = <?= $complaintsPending ?>;
        const complaintsReviewed = <?= $complaintsReviewed ?>;
        const complaintsMonths = <?= json_encode($complaintsMonths) ?>;
        const complaintsData = <?= json_encode($complaintsData) ?>;
        const feedbackData = <?= json_encode($feedbackData) ?>;

        // Complaints doughnut chart
        var complaintChartOptions = {
            chart: { type: 'donut', height: 320 },
            series: [complaintsResolved, complaintsPending, complaintsReviewed],
            labels: ['Resolved', 'Pending', 'Reviewed'],
            colors: ['#10b981', '#ef4444', '#3b82f6'],
            legend: {
                position: 'bottom',
                markers: { radius: 12 },
                itemMargin: { horizontal: 10 }
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '14px', fontWeight: 'bold' }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Complaints',
                                formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0) }
                            }
                        }
                    }
                }
            }
        };
        var complaintChart = new ApexCharts(document.querySelector("#complaintChart"), complaintChartOptions);
        complaintChart.render();

        // Complaints vs Feedback line chart
        var reportTrendOptions = {
            chart: { type: 'line', height: 320, toolbar: { show: false } },
            series: [
                { name: 'Complaints', data: complaintsData },
                { name: 'Feedback', data: feedbackData }
            ],
            xaxis: {
                categories: complaintsMonths,
                labels: {
                    formatter: function (val) {
                        return new Date(val + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    }
                }
            },
            colors: ['#ef4444', '#2563eb'],
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 5 },
            grid: { borderColor: '#e5e7eb', strokeDashArray: 5, position: 'back' },
            legend: { position: 'top', horizontalAlign: 'right' }
        };
        var reportTrend = new ApexCharts(document.querySelector("#reportTrend"), reportTrendOptions);
        reportTrend.render();
        
        // Set the current date in the header
        document.addEventListener('DOMContentLoaded', () => {
            const dateElement = document.getElementById('current-date');
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            dateElement.textContent = new Date().toLocaleDateString('en-US', options);
        });
    </script>
</body>
</html>