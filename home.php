<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <title>Dashboard | Quilana</title>
</head>
<body>
    <?php include 'nav_bar.php'; ?>
    <div class="container-fluid admin">
        <div class="card col-md-12">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <th>Quiz</th>
                        <th>Items</th>
                        <?php if ($_SESSION['login_user_type'] == 3): ?>
                            <th>Status</th>
                        <?php else: ?>
                            <th>Submissions</th>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php 
                        // Build WHERE clause based on user type
                        $where = '';
                        if ($_SESSION['login_user_type'] == 2) {
                            // Faculty
                            $where = " WHERE q.faculty_id = ".$_SESSION['login_id']." ";
                        } elseif ($_SESSION['login_user_type'] == 3) {
                            // Student
                            $where = " WHERE q.quiz_id IN (SELECT quiz_id FROM student_submission WHERE student_id = '".$_SESSION['login_id']."') ";
                        }

                        // Query to fetch quiz list
                        $query = "SELECT q.*, f.firstname AS fname, f.lastname AS lname 
                                  FROM quiz q 
                                  LEFT JOIN faculty f ON q.faculty_id = f.faculty_id 
                                  ".$where." 
                                  ORDER BY q.topic ASC";
                        $result = $conn->query($query);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Count total items in the quiz
                                $items_query = "SELECT COUNT(question_id) AS item_count FROM questions WHERE question_id = '".$row['question_id']."'";
                                $items_result = $conn->query($items_query);
                                $items = $items_result->fetch_assoc()['item_count'];

                                // Determine if quiz is taken or pending for students, or submissions count for faculty
                                $status_column = ($_SESSION['login_user_type'] == 3) ? "status" : "submissions";
                                if ($_SESSION['login_user_type'] == 3) {
                                    $status_query = "SELECT COUNT(student_id) AS taken_count FROM student_submission WHERE quiz_id = '".$row['quiz_id']."' AND student_id = ".$_SESSION['login_id'];
                                } else {
                                    $status_query = "SELECT COUNT(student_id) AS taken_count FROM student_submission WHERE quiz_id = '".$row['quiz_id']."'";
                                }
                                $status_result = $conn->query($status_query);
                                $taken = $status_result->fetch_assoc()['taken_count'];

                                ?>
                                <tr>
                                    <td><?php echo $row['topic'] ?></td>
                                    <td class="text-center"><?php echo $items ?></td>
                                    <td class="text-center"><?php echo ($taken > 0) ? 'Taken' : 'Pending' ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="3" class="text-center">No quizzes found.</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
