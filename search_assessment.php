<?php
include 'db_connect.php';

// Get search query and faculty ID
$query = isset($_GET['query']) ? $_GET['query'] : '';
$faculty_id = isset($_GET['faculty_id']) ? $_GET['faculty_id'] : '';

// Search in assessments
$sql = "SELECT a.*, c.course_name 
        FROM assessment a 
        JOIN course c ON a.course_id = c.course_id 
        WHERE a.faculty_id = ? 
        AND (a.assessment_name LIKE ? 
            OR a.subject LIKE ? 
            OR a.topic LIKE ?
            OR c.course_name LIKE ?)
        ORDER BY c.course_name, a.subject, a.assessment_name ASC";

$stmt = $conn->prepare($sql);
$searchTerm = "%" . $query . "%";
$stmt->bind_param("issss", $faculty_id, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$output = '';
$current_course = '';
$current_subject = '';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $course_name = htmlspecialchars($row['course_name']);
        $subject_name = htmlspecialchars($row['subject']);
        $assessment_name = htmlspecialchars($row['assessment_name']);
        $topic = htmlspecialchars($row['topic']);
        $assessment_id = $row['assessment_id'];

        // Start new course section if course changes
        if ($course_name !== $current_course) {
            if ($current_course !== '') {
                $output .= '</div></div>';
            }
            $output .= '<div class="course-section"><h2>' . $course_name . '</h2>';
            $current_course = $course_name;
            $current_subject = '';
        }

        // Start new subject section if subject changes
        if ($subject_name !== $current_subject) {
            if ($current_subject !== '') {
                $output .= '</div>';
            }
            $output .= '<div class="content-separator">
                        <span class="content-name">' . $subject_name . '</span>
                        <hr class="separator-line">
                    </div>
                    <div class="assessment-container">';
            $current_subject = $subject_name;
        }

        // Add assessment card
        $output .= '<div class="assessment-card">
            <div class="assessment-card-body">
                <div class="meatball-menu-container">
                    <button class="meatball-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="meatball-menu">
                        <div class="arrow-up"></div>
                        <a href="#" class="edit_assessment" data-id="' . $assessment_id . '">
                            <span class="material-symbols-outlined">Edit</span>
                            Edit
                        </a>
                        <a href="#" class="delete_assessment" 
                            data-id="' . $assessment_id . '"
                            data-name="' . $assessment_name . '"
                            data-subject="' . $subject_name . '">
                            <span class="material-symbols-outlined">delete</span>
                            Delete
                        </a>
                    </div>
                </div>
                <div class="assessment-card-title">' . $assessment_name . '</div>
                <div class="assessment-card-topic">Topic: ' . $topic . '</div>
                <div class="assessment-actions">
                    <a id="manage" class="tertiary-button" href="manage_assessment.php?assessment_id=' . $assessment_id . '">Manage</a>
                    <button id="administer" class="main-button" 
                        data-course-id="' . $row['course_id'] . '" 
                        data-course-name="' . $row['course_name'] . '" 
                        data-subject="' . htmlspecialchars($row['subject']) . '" 
                        data-mode="' . htmlspecialchars($row['assessment_mode']) . '" 
                        data-id="' . $row['assessment_id'] . '"
                        data-assessment-name="' . htmlspecialchars($row['assessment_name']) . '">
                        Administer
                    </button>
                </div>
            </div>
        </div>';
    }
    
    // Close the last containers
    if ($current_course !== '') {
        $output .= '</div></div>';
    }
} else {
    $output = '<div class="no-records">No assessments found matching your search.</div>';
}

echo $output;
?>