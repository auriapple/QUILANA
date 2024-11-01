<?php
include('db_connect.php');
include('auth.php');

header('Content-Type: application/json');

if(isset($_POST['course_id']) && isset($_POST['faculty_id'])){
    $course_id = $_POST['course_id'];
    $faculty_id = $_POST['faculty_id'];

    $conn->begin_transaction();

    try {
        // Disable foreign key checks if necessary
        $conn->query("SET FOREIGN_KEY_CHECKS=0");

        // Delete from associated tables first
        $conn->query("DELETE FROM administer_assessment WHERE course_id = '$course_id'");
        $conn->query("DELETE FROM join_assessment WHERE course_id = '$course_id'");
        $conn->query("DELETE FROM class WHERE course_id = '$course_id'");
        
        // Delete course
        $qry = $conn->query("DELETE FROM course WHERE course_id='$course_id' AND faculty_id='$faculty_id'");

        if ($qry) {
            $conn->commit();
            echo json_encode(['status' => 1]);
        } else {
            throw new Exception('Failed to delete course');
        }

        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1");

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 0, 'msg' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 0, 'msg' => 'Required data is missing.']);
}
?>
