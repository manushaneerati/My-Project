<?php
session_start();

// Initialize goals in session if not already set
if (!isset($_SESSION['goals'])) {
    $_SESSION['goals'] = [];
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header("location: login.php");
}

// Handle adding a new goal
if (isset($_POST['add_goal'])) {
    $goalText = trim($_POST['goal_text']);
    $targetDays = trim($_POST['target_days']);
    if ($goalText && $targetDays) {
        $startDate = date('Y-m-d'); // Store the current date as the start date
        $_SESSION['goals'][] = [
            'text' => $goalText,
            'progress' => 0,
            'start_date' => $startDate, // Add start date
            'target_days' => $targetDays // Add target days
        ];
    }
    header('location: index.php'); // Redirect to refresh the page with updated goals
}

// Handle removing a goal
if (isset($_GET['remove_goal'])) {
    $goalIndex = $_GET['remove_goal'];
    unset($_SESSION['goals'][$goalIndex]);
    $_SESSION['goals'] = array_values($_SESSION['goals']); // Reindex the array
    header('location: index.php'); // Redirect to refresh the page
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal Tracker Dashboard</title>
    <!-- Include Material Icons from Google -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <style>
        /* General Styles for the Sidebar */
        #sidebar {
            width: 250px;
            background-color: midnightblue; 
            color: white;
            height: 100vh; 
            padding: 20px;
            font-family: 'Arial', sans-serif; 
            box-shadow: 2px 0px 10px rgba(0, 0, 0, 0.1); 
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;

        }

        /* Main Content Section (beside sidebar) */
        .main-content {
            margin-left: 270px; /* Add space to the left for the sidebar */
            padding: 20px;
            text-align: center;
            font-size: 20px;
        }

        /* Calendar Section */
        #calendar-section {
            background-color: MistyRose;
            padding: 10px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }

        #calendar-header {
            text-align: center;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .goal-list {
            margin-top: 20px;
            text-align: left;
        }

        .goal-item {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            background-color: #f8f8f8;
            border-radius: 5px;
        }

        footer {
            background-color: pink;
            height: 8vh;
        }

        header {
            background-color: pink;
            color: white;
            padding: 20px;
            text-align: center;
            margin-left: 290px;
        }

        /* Remove underline from the 'Remove Goal' link */
        .remove-goal {
            text-decoration: none;
            color: red;
        }
    </style>
</head>

<body>
    <header>
        <h1>Goal Tracker</h1>
    </header>

    <div class="container" style="display: flex;">
        <!-- Sidebar -->
        <div id="sidebar">
            <div class="top">
                <div class="logo">
                    <img id="logo" src="images/logo1.jpg" alt="Logo" width="100" height="60">
                    <h2>Goal <span class="pink">Tracker</span></h2>
                </div>
            </div>

            <a href="profile.php"><i class="material-icons">person</i>Profile</a><br>
            <label for="goal-type"><i class="material-icons">star</i>Goal Type:</label>
            <select id="goal-type" name="goal-type">
                <option value="short-term">Short-Term</option>
                <option value="long-term">Long-Term</option>
            </select><br>

            <!-- Calendar Link -->
            <a href="javascript:void(0)" id="calendar-link"><i class="material-icons">calendar_today</i>Calendar</a><br>

            <div id="calendar-section">
                <div id="calendar-header">
                    <span id="month-year"></span>
                    <select id="month-select" onchange="updateCalendar()">
                        <!-- Month options will be populated by JavaScript -->
                    </select>
                    <select id="year-select" onchange="updateCalendar()">
                        <!-- Year options will be populated by JavaScript -->
                    </select>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                        </tr>
                    </thead>
                    <tbody id="calendar-body">
                        <!-- Days of the current month will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div id="selected-date">
                <h4>Selected Date: <span id="date-display">None</span></h4>
            </div>

            <p><a href="index.php?logout='1'" style="color: white;"><i class="material-icons">exit_to_app</i>Logout</a></p>
        </div>

        <!-- Main Section -->
        <div class="main-content" id="main-section">
            <h2>Your Goals</h2>

            <!-- Add Goal Form -->
            <form action="index.php" method="POST">
                <input type="text" name="goal_text" placeholder="Enter a new goal" required>
                <input type="number" name="target_days" placeholder="Enter target days" required min="1">
                <button type="submit" name="add_goal">Add Goal</button>
            </form>

            <div class="goal-list">
                <?php if (count($_SESSION['goals']) > 0): ?>
                    <?php foreach ($_SESSION['goals'] as $index => $goal): ?>
                        <div class="goal-item">
                            <p><strong><?php echo $goal['text']; ?></strong></p>
                            <p>Target days: <?php echo $goal['target_days']; ?> days</p>

                            <?php
                            // Calculate the number of days spent on the goal
                            $startDate = new DateTime($goal['start_date']);
                            $currentDate = new DateTime();
                            $daysSpent = $startDate->diff($currentDate)->days;
                            ?>

                            <p>Days spent: <?php echo $daysSpent; ?> days</p>

                            <a href="index.php?remove_goal=<?php echo $index; ?>" class="remove-goal">Remove Goal</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No goals yet. Add one above!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Toggle the visibility of the calendar section
        document.getElementById('calendar-link').addEventListener('click', function () {
            const calendarSection = document.getElementById('calendar-section');
            if (calendarSection.style.display === 'none' || calendarSection.style.display === '') {
                calendarSection.style.display = 'block';
                generateCalendar(); // Generate the calendar when it's displayed
            } else {
                calendarSection.style.display = 'none';
            }
        });

        function updateCalendar() {
            const month = parseInt(document.getElementById('month-select').value);
            const year = parseInt(document.getElementById('year-select').value);
            generateCalendar(month, year);
        }

        function generateCalendar(month = new Date().getMonth(), year = new Date().getFullYear()) {
            const calendarBody = document.getElementById('calendar-body');
            const calendarHeader = document.getElementById('month-year');
            const monthSelect = document.getElementById('month-select');
            const yearSelect = document.getElementById('year-select');

            const firstDayOfMonth = new Date(year, month, 1);
            const lastDateOfMonth = new Date(year, month + 1, 0);
            const totalDays = lastDateOfMonth.getDate();
            const firstDay = firstDayOfMonth.getDay();

            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            calendarHeader.innerHTML = `${months[month]} ${year}`;

            monthSelect.innerHTML = '';
            months.forEach((m, i) => {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = m;
                if (i === month) option.selected = true;
                monthSelect.appendChild(option);
            });

            const currentYear = new Date().getFullYear();
            yearSelect.innerHTML = '';
            for (let i = currentYear - 5; i <= currentYear + 5; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                if (i === year) option.selected = true;
                yearSelect.appendChild(option);
            }

            let calendarHTML = '';
            let dayCounter = 1;

            calendarHTML += '<tr>';
            for (let i = 0; i < firstDay; i++) {
                calendarHTML += '<td></td>';
            }

            for (let i = firstDay; i < 7; i++) {
                calendarHTML += `<td onclick="showDay('${['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][i]}', ${dayCounter})">${dayCounter}</td>`;
                dayCounter++;
            }
            calendarHTML += '</tr>';

            while (dayCounter <= totalDays) {
                calendarHTML += '<tr>';
                for (let i = 0; i < 7 && dayCounter <= totalDays; i++) {
                    calendarHTML += `<td onclick="showDay('${['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][i]}', ${dayCounter})">${dayCounter}</td>`;
                    dayCounter++;
                }
                calendarHTML += '</tr>';
            }

            calendarBody.innerHTML = calendarHTML;
        }

        function showDay(day, date) {
            const dateDisplay = document.getElementById("date-display");
            dateDisplay.innerText = `${day}, ${date}`;
        }

        window.onload = function () {
            const currentMonth = new Date().getMonth();
            const currentYear = new Date().getFullYear();
            generateCalendar(currentMonth, currentYear);
        };
    </script>
</body>
</html>
