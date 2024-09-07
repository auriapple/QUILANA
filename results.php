<?php
include('db_connect.php');
include('auth.php'); // Make sure the user is logged in

if (isset($_GET['assessment_id'])) {
    $assessment_id = $conn->real_escape_string($_GET['assessment_id']);
    $student_id = $_SESSION['login_id'];

    // Fetch the student's result from the student_result table
    $result_query = $conn->query("SELECT sr.score, sr.date_taken, a.assessment_name 
                                  FROM student_result sr
                                  JOIN assessment a ON sr.assessment_id = a.assessment_id
                                  WHERE sr.student_id = '$student_id' AND sr.assessment_id = '$assessment_id'");

    if ($result_query->num_rows > 0) {
        $result = $result_query->fetch_assoc();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <?php include('header.php') ?>
            <title>Assessment Result | Quilana</title>
        </head>
        <body>
            <?php include('nav_bar.php') ?>
            <div class="container-fluid admin">
                <div class="result-container">
                    <h3>Assessment: <?php echo htmlspecialchars($result['assessment_name']); ?></h3>
                    <p>Score: <?php echo htmlspecialchars($result['score']); ?></p>
                    <p>Date Taken: <?php echo htmlspecialchars($result['date_taken']); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "<p>No result found for this assessment.</p>";
    }
} else {
    echo "<p>Invalid assessment ID.</p>";
}
?>
