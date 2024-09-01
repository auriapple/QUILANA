<?php
include('db_connect.php'); // Include database connection

// Get the search query from the request
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Prepare and execute the search query
$sql = "SELECT a.*, c.course_name, cl.subject 
        FROM assessment a 
        JOIN class cl ON a.class_id = cl.class_id 
        JOIN course c ON cl.course_id = c.course_id 
        WHERE a.faculty_id = ? 
        AND c.course_name LIKE ? 
        ORDER BY c.course_name, cl.subject, a.assessment_name ASC";

// Prepare the statement
$stmt = $conn->prepare($sql);
$searchTerm = '%' . $query . '%'; // Add wildcards for partial matching
$stmt->bind_param('ss', $_SESSION['login_id'], $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$current_course = '';
$current_subject = '';

while ($row = $result->fetch_assoc()) {
    $course_name = htmlspecialchars($row['course_name']);
    $subject_name = htmlspecialchars($row['subject']);
    $assessment_name = htmlspecialchars($row['assessment_name']);
    $topic = htmlspecialchars($row['topic']);
    $assessment_id = $row['assessment_id'];
?>

    <?php if ($course_name !== $current_course) { ?>
        <?php if ($current_course !== '') { ?></div><?php } ?>
        <div class="course-section">
            <h2><?php echo $course_name; ?></h2>
    <?php 
        $current_course = $course_name;
        $current_subject = '';
    } ?>

    <?php if ($subject_name !== $current_subject) { ?>
        <?php if ($current_subject !== '') { ?></div><?php } ?>
        <div class="subject-header">
            <span><?php echo $subject_name; ?></span>
            <div class="line"></div>
        </div>
        <div class="course-cards-container">
    <?php 
        $current_subject = $subject_name;
    } ?>

    <div class="course-card">
        <div class="course-card-body">
            <div class="meatball-menu-container">
                <button class="meatball-menu-btn">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </button>
                <div class="meatball-menu">
                    <a href="#" class="edit_assessment" data-id="<?php echo $assessment_id ?>">Edit</a>
                    <a href="#" class="delete_assessment" data-id="<?php echo $assessment_id ?>">Delete</a>
                </div>
            </div>
            <div class="course-card-title"><?php echo $assessment_name; ?></div>
            <div class="course-card-text"><br>Topic: <br><?php echo $topic; ?></div>
            <div class="course-actions">
                <button class="btn btn-outline-primary btn-sm view_assessment_details" data-id="<?php echo $assessment_id ?>">View Details</button>
                <button class="btn btn-primary btn-sm administer" data-id="<?php echo $assessment_id ?>">Administer</button>
            </div>
        </div>
    </div>

<?php
}
$stmt->close();
$conn->close();
?>
