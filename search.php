<?php
include 'db_connect.php';

// Get search query and type
$query = isset($_GET['query']) ? $_GET['query'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : ''; // 'courses' or 'classes'


if ($type == 'courses') {
    // Search in courses
    $sql = "SELECT * FROM course 
            WHERE  course_name LIKE ?
            ORDER BY course_name ASC";
    
    
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        echo 'Error: ' . $stmt->error;
        exit;
    }
    
    $output = '';
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $course_id = $row['course_id'];
            $classCountQuery = $conn->query("SELECT COUNT(*) as classCount FROM class WHERE course_id = '$course_id'");
            $classCountRow = $classCountQuery->fetch_assoc();
            $classCount = $classCountRow['classCount'];
            
            $output .= '
            <div class="course-card">
                <div class="course-card-body">
                    <div class="meatball-menu-container">
                        <button class="meatball-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="meatball-menu">
                            <div class="arrow-up"></div>
                            <a href="#" class="edit_course" data-id="'.$row['course_id'].'" data-name="'.$row['course_name'].'">
                                <span class="material-symbols-outlined">Edit</span>Edit</a>
                            <a href="#" class="delete_course" data-id="'.$row['course_id'].'" data-name="'.$row['course_name'].'">
                                <span class="material-symbols-outlined">delete</span>Delete</a>
                        </div>
                    </div>
                    <div class="course-card-title">'.$row['course_name'].'</div>
                    <div class="course-card-text">'.$classCount.' Class(es)</div>
                    <div class="course-actions">
                        <button id="viewClasses" class="tertiary-button viewClasses" data-id="'.$row['course_id'].'" data-name="'.$row['course_name'].'" type="button">Classes</button>
                        <button id="viewCourseDetails" class="main-button" data-id="'.$row['course_id'].'" type="button">View Details</button>
                    </div>
                </div>
            </div>';
        }
    } else {
        $output = '<div class="no-records">No programs found matching your search.</div>';
    }
    
} else {
    // Search in classes for the current course
    $course_id = isset($_GET['course_id']) ? $_GET['course_id'] : 0;
    
    $sql = "SELECT * FROM class 
            WHERE course_id = ? 
            AND (class_name LIKE ? OR subject LIKE ?)
            ORDER BY class_name ASC";
    
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("iss", $course_id, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $output = '';
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= '
            <div class="class-card">
                <div class="class-card-body">
                    <div class="meatball-menu-container">
                        <button class="meatball-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="meatball-menu">
                            <div class="arrow-up"></div>
                            <a href="#" class="edit_class" 
                                data-course-id="'.$row['course_id'].'"
                                data-class-id="'.$row['class_id'].'"
                                data-class-name="'.$row['class_name'].'"
                                data-subject="'.$row['subject'].'">
                                <span class="material-symbols-outlined">Edit</span>Edit</a>
                            <a href="#" class="delete_class"
                                data-course-id="'.$row['course_id'].'"
                                data-class-id="'.$row['class_id'].'"
                                data-class-name="'.$row['class_name'].'"
                                data-subject="'.$row['subject'].'">
                                <span class="material-symbols-outlined">delete</span>Delete</a>
                            <a href="#" class="get_code"
                                data-class-id="'.$row['class_id'].'"
                                data-class-name="'.$row['class_name'].'"
                                data-subject="'.$row['subject'].'">
                                <span class="material-symbols-outlined">key</span>Get Code</a>
                        </div>
                    </div>
                    <div class="class-card-title">'.$row['class_name'].'</div>
                    <div class="class-card-text">Course Subject: '.$row['subject'].'</div>
                    <div class="class-actions">
                        <button id="viewClassDetails" class="main-button" data-id="'.$row['class_id'].'" type="button">View Details</button>
                    </div>
                </div>
            </div>';
        }
    } else {
        $output = '<div class="no-records">No classes found matching your search.</div>';
    }
}

echo $output;
?>