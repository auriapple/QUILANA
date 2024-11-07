<?php
// search_classes.php
include('db_connect.php');

if (isset($_GET['query']) && isset($_GET['student_id'])) {
    $search = mysqli_real_escape_string($conn, $_GET['query']);
    $student_id = mysqli_real_escape_string($conn, $_GET['student_id']);
    
    $query = "SELECT c.class_id, c.subject, c.class_name, f.firstname, f.lastname 
              FROM student_enrollment e
              JOIN class c ON e.class_id = c.class_id
              JOIN faculty f ON c.faculty_id = f.faculty_id
              WHERE e.student_id = '$student_id' 
              AND e.status = '1'";
    
    // Search Conditions
    if (!empty($search)) {
        $query .= " AND (
            c.subject LIKE '%$search%' OR 
            c.class_name LIKE '%$search%' OR 
            CONCAT(f.firstname, ' ', f.lastname) LIKE '%$search%'
        )";
    }

    $result = $conn->query($query);
    
    echo '<div class="class-container">';
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="class-card" id="class_<?php echo $row['class_id']; ?>">
                <div class="meatball-menu-container">
                    <button class="meatball-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="meatball-menu">
                        <div class="arrow-up"></div>
                        <a href="#" class="unenroll"
                            data-id="<?php echo $row['class_id'] ?>"
                            data-name="<?php echo $row['class_name'] ?>">
                            <span class="material-symbols-outlined">exit_to_app</span>
                            Unenroll</a>
                        <a href="#" class="report">
                            <span class="material-symbols-outlined">report</span>
                            Report</a>
                    </div>
                </div>
                <div class="class-card-title"><?php echo $row['subject'] ?></div>
                <div class="class-card-text">Section: <?php echo $row['class_name'] ?> <br>Professor: <?php echo $row['firstname'] . ' ' . $row['lastname'] ?></div>
                <div class="class-actions">
                    <button id="viewClassDetails_<?php echo $row['class_id']; ?>" class="main-button" data-id="<?php echo $row['class_id'] ?>" type="button">View Class</button>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="no-records">No classes found matching your search</div>';
    }
    
    echo '</div>'; 
}
?>