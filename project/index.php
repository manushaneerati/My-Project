<?php
// Start the session
session_start();

// Database connection (Replace with your actual database credentials)
$host = 'localhost';
$dbname = 'project';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use session username
$current_username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Add goal
if (isset($_POST['add_goal'])) {
    $goal_text = $_POST['goal_text'];
    $target_days = $_POST['target_days'];

    $stmt = $conn->prepare("INSERT INTO goals (goal_text, target_days, username) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $goal_text, $target_days, $current_username);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

// Remove goal
if (isset($_GET['remove_goal'])) {
    $goal_id = $_GET['remove_goal'];

    $stmt = $conn->prepare("DELETE FROM goals WHERE id = ?");
    $stmt->bind_param("i", $goal_id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

// Fetch user's goals by username
$stmt = $conn->prepare("SELECT id, goal_text, target_days, username FROM goals WHERE username = ?");
$stmt->bind_param("s", $current_username);
$stmt->execute();
$result = $stmt->get_result();

$goals = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $goals[] = $row;
    }
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

        /* Goal List Styles */
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
        }

        .reminder-section {
            margin-top: 20px;
            padding: 10px;
            background-color: #444;
            border-radius: 5px;
        }

        .reminder-section input[type="text"] {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .reminder-section button {
            width: 100%;
            padding: 5px;
            background-color: #ff9900;
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .reminder-text {
            font-size: 14px;
            margin-top: 10px;
            color: #ff9900;
        }
        
        /* Sidebar Links - Make sure Profile and Calendar text is white */
        #sidebar a {
            color: white; /* Set text color to white */
            text-decoration: none; /* Remove underline from links */
            display: block; /* Make links block-level to occupy the full width */
            margin: 10px 0; /* Add margin for spacing */
        }

        /* Optionally, you can change the hover color to a different shade if needed */
        #sidebar a:hover {
            color: #ff9900; /* Change color to orange on hover for better visibility */
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
          <!--<a href="javascript:void(0)" id="calendar-link"><i class="material-icons">calendar_today</i>Calendar</a><br>-->


           <div id="calendar-section" style="display: none;">
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

                      <p><a href="login.php?logout='1'" style="color: white;"><i class="material-icons">exit_to_app</i>Logout</a></p>
        </div>

        <!-- Main Section -->
        <div class="main-content" id="main-section">
            <h2>Your Goals</h2>
  
            <!-- Add Goal Form -->
            <form action="index.php" method="POST">
                <input type="text" name="goal_text" placeholder="Enter a new goal" required>
                <input type="number" name="target_days" placeholder="Target Days" required> <!-- Added target days input -->
                <button type="submit" name="add_goal">Add Goal</button>
            </form>

            <div class="goal-list">
                <?php if (count($goals) > 0): ?>
                    <?php foreach ($goals as $goal): ?>
                       <div class="goal-item" data-goal-id="<?php echo $goal['id']; ?>">
    <p><strong><?php echo $goal['goal_text']; ?></strong></p>
    <p>Target Days: <?php echo $goal['target_days']; ?> days</p>
    <p>Added by: <?php echo $goal['username']; ?></p>
</div>
 <!--<progress id="progressBar" value="0" max="100"></progress>
         <p id="progressText">Progress: 0%</p>-->

                 
   <?php endforeach; ?>
                <?php else: ?>
                    <p>No goals yet. Add one above!</p>
       
      
                <?php endif; ?>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const goals = document.querySelectorAll('.goal-item');

    goals.forEach((goalItem) => {
        const goalId = goalItem.dataset.goalId = goalItem.dataset.goalId || 
            (goalItem.querySelector('a.remove-goal')?.href.match(/remove_goal=(\d+)/)?.[1] || '');

        const targetDays = parseInt(goalItem.querySelector('p:nth-child(2)').textContent.match(/\d+/)[0]);

        // Remove any pre-existing remove link from PHP
        const oldRemoveLink = goalItem.querySelector('.remove-goal');
        if (oldRemoveLink) oldRemoveLink.remove();

        // Create date picker
        const dateInput = document.createElement('input');
        dateInput.type = 'date';
        dateInput.placeholder = 'Select start date';
        goalItem.appendChild(dateInput);

        // Container for checkboxes
        const daysContainer = document.createElement('div');
        goalItem.appendChild(daysContainer);

        // Create progress bar
        const progressBar = document.createElement('progress');
        progressBar.value = 0;
        progressBar.max = 100;
        goalItem.appendChild(progressBar);

        // Create progress text
        const progressText = document.createElement('p');
        progressText.textContent = 'Progress: 0%';
        goalItem.appendChild(progressText);

        // Create remove link
        const removeLink = document.createElement('a');
        removeLink.href = `index.php?remove_goal=${goalId}`;
        removeLink.className = 'remove-goal';
        removeLink.textContent = 'Remove Goal';
        removeLink.style.color = 'red';
        removeLink.style.display = 'block';
        removeLink.style.marginTop = '10px';
        goalItem.appendChild(removeLink);

        let allDates = [];
        let checkedDates = [];

        dateInput.addEventListener('change', () => {
            daysContainer.innerHTML = '';
            allDates = [];
            checkedDates = [];

            const startDate = new Date(dateInput.value);
            if (isNaN(startDate)) return;

            for (let i = 0; i < targetDays; i++) {
                const date = new Date(startDate);
                date.setDate(startDate.getDate() + i);
                const dateStr = date.toISOString().split('T')[0];
                allDates.push(dateStr);

                const label = document.createElement('label');
                label.style.display = 'block';
                label.style.margin = '5px 0';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.dataset.date = dateStr;

                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        checkedDates.push(dateStr);
                    } else {
                        checkedDates = checkedDates.filter(d => d !== dateStr);
                    }
                    updateProgress();
                });

                label.appendChild(checkbox);
                label.append(` ${dateStr}`);
                daysContainer.appendChild(label);
            }

            updateProgress();
        });

        function updateProgress() {
            const progress = (checkedDates.length / allDates.length) * 100;
            progressBar.value = progress;
            progressText.textContent = `Progress: ${progress.toFixed(2)}%`;
        }
    });
});
</script>
</body>
</html> 
