<?php
include('db_connect.php');
include('auth.php');

// Check if assessment_id is set in URL
if (!isset($_GET['assessment_id'])) {
    $redirect_page = isset($_GET['assessment_mode']) && $_GET['assessment_mode'] === '3' ? 'assessment_mode_3.php' : 'assessment_mode_2.php';
    header("Location: $redirect_page");
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);
$student_id = $_SESSION['login_id'];

// Fetch assessment details
$assessment_query = $conn->query("
    SELECT a.assessment_mode, aa.administer_id, a.assessment_name, aa.ranks_status
    FROM assessment a
    JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
    WHERE a.assessment_id = '$assessment_id'
");
$assessment = $assessment_query->fetch_assoc();
$assessment_name = $assessment['assessment_name'];
$assessment_mode = $assessment['assessment_mode'];
$administer_id = $assessment['administer_id'];
$ranks_status = $assessment['ranks_status'];

// Fetch all students' status
$status_query = $conn->query("
    SELECT status
    FROM join_assessment
    WHERE administer_id = '$administer_id'
");

// Count how many students have completed (status = 2)
$total_students = $status_query->num_rows;
$completed_count = 0;

while ($row = $status_query->fetch_assoc()) {
    if ($row['status'] == 2) {
        $completed_count++;
    }
}

// Check if all students are finished
$all_completed = ($completed_count == $total_students);

$display = '';
// Calculate Rank
if ($all_completed) {
    if (!$ranks_status) {
        ob_start();
        include('get_ranking.php');
        $status = ob_get_clean();

        if(trim($status) !== "success") {
            $display = 'waiting';
        }
        $display = 'waiting';

        // Fetch student score
        /*$score_query = $conn->query("
            SELECT score 
            FROM student_results 
            WHERE assessment_id = '$assessment_id' 
            AND student_id = '$student_id'
        ");
        $score_row = $score_query->fetch_assoc();
        $total_score = $score_row['score'];

        // Fetch student details and rank for the assessment
        $student_query = $conn->query("
            SELECT s.firstname, sr.rank, sr.score, s.student_id
            FROM student s
            JOIN student_results sr ON s.student_id = sr.student_id
            WHERE sr.assessment_id = '$assessment_id' AND sr.student_id = '$student_id'
        ");

        // Check if student data was found
        if ($student_query && $student_query->num_rows > 0) {
            $student_data = $student_query->fetch_assoc();
        } else {
            // Handle the case where no student data was found
            echo "<p>No results found for this assessment.</p>";
            exit;
        }

        // Fetch leaderboard details
        $leaderboard_query = $conn->query("
            SELECT s.firstname, s.lastname, sr.score, sr.rank, s.student_id
            FROM student s
            JOIN student_results sr ON s.student_id = sr.student_id
            WHERE sr.assessment_id = '$assessment_id'
            ORDER BY sr.rank ASC
        ");

        // Group leaderboard data based on rank
        $grouped_data = [];
        while ($row = $leaderboard_query->fetch_assoc()) {
            $grouped_data[$row['rank']][] = $row;
        }
        //Sets the rank suffix based on the rank number
        $rank_suffix = getRankSuffix($student_data['rank']);

        // Displays the leaderboard when the view leaderboard button is clicked
        $display = isset($_POST['view_leaderboard']) ? 'leaderboard' : 'ranking';*/
    } else {
        // Fetch student score
        $score_query = $conn->query("
            SELECT score 
            FROM student_results 
            WHERE assessment_id = '$assessment_id' 
            AND student_id = '$student_id'
        ");
        $score_row = $score_query->fetch_assoc();
        $total_score = $score_row['score'];

        // Fetch student details and rank for the assessment
        $student_query = $conn->query("
            SELECT s.firstname, sr.rank, sr.score, s.student_id
            FROM student s
            JOIN student_results sr ON s.student_id = sr.student_id
            WHERE sr.assessment_id = '$assessment_id' AND sr.student_id = '$student_id'
        ");

        // Check if student data was found
        if ($student_query && $student_query->num_rows > 0) {
            $student_data = $student_query->fetch_assoc();
            error_log('Fetched student data: ' . print_r($student_data, true));
        } else {
            // Handle the case where no student data was found
            echo "<p>No results found for this assessment.</p>";
            exit;
        }

        // Fetch leaderboard details
        $leaderboard_query = $conn->query("
            SELECT s.firstname, s.lastname, sr.score, sr.rank, s.student_id
            FROM student s
            JOIN student_results sr ON s.student_id = sr.student_id
            WHERE sr.assessment_id = '$assessment_id'
            ORDER BY sr.rank ASC
        ");

        // Group leaderboard data based on rank
        $grouped_data = [];
        while ($row = $leaderboard_query->fetch_assoc()) {
            $grouped_data[$row['rank']][] = $row;
        }
        //Sets the rank suffix based on the rank number
        $rank_suffix = getRankSuffix($student_data['rank']);

        // Displays the leaderboard when the view leaderboard button is clicked
        $display = isset($_POST['view_leaderboard']) ? 'leaderboard' : 'ranking';
    }
} else {
    $display = 'waiting';
}


// Function to get rank suffix
function getRankSuffix($rank) {
    if ($rank % 10 == 1 && $rank % 100 != 11) {
        return "st";
    } elseif ($rank % 10 == 2 && $rank % 100 != 12) {
        return "nd";
    } elseif ($rank % 10 == 3 && $rank % 100 != 13) {
        return "rd";
    }
    return "th";
}

// DUMMY DATA (Uncomment to use)
/*$student_data = [
    'firstname' => 'John',
    'rank' => 1,
    'score' => 95,
    'student_id' => $student_id
];

// Dummy leaderboard data
$grouped_data = [
    1 => [
        ['firstname' => 'John', 'lastname' => 'Doe', 'score' => 95, 'rank' => 1, 'student_id' => $student_id],
        ['firstname' => 'Oliver', 'lastname' => 'Twist', 'score' => 95, 'rank' => 1, 'student_id' => 4]
    ],
    2 => [
        ['firstname' => 'Jane', 'lastname' => 'Smith', 'score' => 90, 'rank' => 2, 'student_id' => 5]
    ],
    3 => [
        ['firstname' => 'Michael', 'lastname' => 'Brown', 'score' => 85, 'rank' => 3, 'student_id' => 6],
        ['firstname' => 'Emily', 'lastname' => 'Johnson', 'score' => 85, 'rank' => 3, 'student_id' => 7]
    ],
    4 => [
        ['firstname' => 'Sophia', 'lastname' => 'Williams', 'score' => 82, 'rank' => 4, 'student_id' => 8],
        ['firstname' => 'James', 'lastname' => 'Taylor', 'score' => 82, 'rank' => 4, 'student_id' => 9]
    ],
    5 => [
        ['firstname' => 'Lucas', 'lastname' => 'Martinez', 'score' => 80, 'rank' => 5, 'student_id' => 10],
        ['firstname' => 'Ava', 'lastname' => 'Garcia', 'score' => 80, 'rank' => 5, 'student_id' => 11]
    ]
];*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assessment_name); ?> | Quilana</title>
    <?php include('header.php'); ?>
    <link rel="stylesheet" href="assets/css/ranking.css">
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="content-wrapper">
        <!-- Close Button -->
        <div class="header-container">
            <?php if ($display === 'leaderboard'): ?>
                <!-- Redirects to the results page -->
                <form method="POST" action="results.php">
                    <button type="submit" id="close" class="secondary-button">Close</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Displays the assessment name on a tab -->
        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab-link active" data-tab="assessment-tab"><?php echo htmlspecialchars($assessment_name); ?></li>
            </ul>
        </div>

        <!-- Displays the personal ranking by default -->
        <?php if ($display === 'waiting'): ?>
            <div id="waiting-container" class="ranking-container">
                <h3>Congratulations on finishing the quiz!</h3>
                <h5>Take a breather while waiting for others to finish</h5>
            </div>
        <?php elseif ($display === 'ranking'): ?>
            <div id="personal-ranking-container" class="ranking-container">
                <div class="ranking-flag-image">
                    <div class="ranking-flag-content">
                        <h3><?php echo htmlspecialchars($student_data['firstname']); ?></h3>
                        <h4>Rank</h4>
                        <h3><?php echo htmlspecialchars($student_data['rank']) ?></h3>               
                    </div>   
                </div>          
                <h3>Congratulations! You ranked <?php echo htmlspecialchars($student_data['rank']) . $rank_suffix; ?> in this quiz</h3>
                <p>Check out the leaderboard</p>
                <!-- Switches to leaderboard view -->
                <form method="POST">
                    <button type="submit" id="viewLeaderboard" name="view_leaderboard" class="secondary-button">View Leaderboard</button>
                </form>
            </div>

        <!-- Displays the leaderboard once the view leaderboard button is clicked -->
        <?php elseif ($display === 'leaderboard'): ?>
            <div id="leaderboards-container" class="ranking-container">
                <h3>Leaderboard</h3>
                <?php
                $top_entries = [];
                foreach ($grouped_data as $rank => $students) {
                    if ($rank <= 3) {
                        $count = count($students);
                        if ($count > 0) {
                            // If there's only one student, display their name
                            if ($count === 1) {
                                $top_entries[$rank] = [
                                    'name' => htmlspecialchars($students[0]['firstname']),
                                    'score' => htmlspecialchars($students[0]['score']),
                                    'count' => $count
                                ];
                            } 
                            // If there are two students, display both names
                            elseif ($count === 2) {
                                $names = array_map('htmlspecialchars', array_column($students, 'firstname'));
                                $scores = array_map('htmlspecialchars', array_column($students, 'score'));
                                $top_entries[$rank] = [
                                    'name' => implode(' & ', $names),
                                    'score' => htmlspecialchars($students[0]['score']),
                                    'count' => $count
                                ];
                            } 
                            // If there are three or more students, display the first name and count the others
                            else {
                                $names = array_map('htmlspecialchars', array_column($students, 'firstname'));
                                $scores = array_map('htmlspecialchars', array_column($students, 'score'));
                                $top_entries[$rank] = [
                                    'name' => $names[0] . ' & ' . ($count - 1) . ' others',
                                    'score' => htmlspecialchars($students[0]['score']),
                                    'count' => $count
                                ];
                            }
                        }
                    }
                }

                // Adjust the order: Top 2, Top 1, Top 3
                $ordered_top_entries = [
                    isset($top_entries[2]) ? $top_entries[2] : null,
                    isset($top_entries[1]) ? $top_entries[1] : null,
                    isset($top_entries[3]) ? $top_entries[3] : null,
                ];
                // Top 3 Platfrom
                echo "<div class='ranking-platform'>";
                echo "<div class='ranking-platform-content'>";
                foreach ($ordered_top_entries as $index => $entry) {
                    if ($entry) {
                        $top_name = $entry['name'];
                        $top_score = $entry['score'];
                        $student_count = $entry['count'];

                        $score_color = '';
                        $name_font_size = '';
                        $score_font_size = '';
                        $padding_top = '';

                        if ($index === 1) {
                            $score_color = '#BF88EC';
                            $name_font_size = '22px';
                            $score_font_size = '16px';
                        } elseif ($index === 0) {
                            $score_color = '#C44C68';
                            $name_font_size = '18px';
                            $score_font_size = '12px';
                        } elseif ($index === 2) {
                            $score_color = '#EC7735';
                            $name_font_size = '18px';
                            $score_font_size = '12px';
                        }

                        // Set padding top based on student count
                        if ($student_count > 1){
                            if ($index === 1) {
                                $padding_top = '65px'; //35px
                            } else {
                                $padding_top = '100px'; //100px
                            }
                        } else {
                            if ($index === 1) {
                                $padding_top = '85px'; //35px
                            } else {
                                $padding_top = '115px'; //100px
                            }
                        }

                        echo "<div class='top-entry' style='padding-top: $padding_top;'>
                                <span class='top-name' style='font-size: $name_font_size;'>$top_name</span><br>
                                <span class='top-score' style='color: $score_color; font-size: $score_font_size;'>$top_score points</span>
                        </div>";
                    }
                }
                echo "</div>";
                echo "</div>";

                // Leaderboard Container
                echo "<div class='leaderboard-group-container'>";
                foreach ($grouped_data as $rank => $students) {
                    // Rank Separator
                    echo "<div class='rank-container'>";
                    echo "<hr class='separator-line'>";
                    echo "<span class='rank'>Rank $rank</span>";
                    echo "<hr class='separator-line'>";
                    echo "</div>";
                    // Displays the name and score of students
                    foreach ($students as $student) {
                        $full_name = htmlspecialchars($student['firstname'] . ' ' . $student['lastname']);
                        $score_display = htmlspecialchars($student['score']);
                        // Checks the student id and highlights the record
                        $is_highlighted = ($student['student_id'] == $student_id) ? 'highlighted-entry' : '';
                        echo "<div class='leaderboard-entry $is_highlighted'>
                                <span class='leaderboard-name'>$full_name</span>
                                <span class='leaderboard-score'>$score_display points</span>
                        </div>";
                    }
                }
                echo "</div>";
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>