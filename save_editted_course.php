<?php
include('db_connect.php');
include('auth.php');

// Set content type to JSON
header('Content-Type: application/json');

if(isset($_POST['course_name'])){
    $course_id = isset($_POST['course_id']) ? $_POST['course_id'] : '';
    $faculty_id = $_POST['faculty_id'];
    $course_name = $_POST['course_name'];

    if (!empty($course_id)) {
        $qry = $conn->query("UPDATE course SET course_name='$course_name' WHERE course_id='$course_id' AND faculty_id='$faculty_id'");
    } else {
        echo json_encode(['status' => 0, 'msg' => 'Course ID is missing.']);
        exit;
    }

    if($qry){
        echo json_encode(['status' => 1]);
    } else {
        echo json_encode(['status' => 0, 'msg' => 'Failed to save course']);
    }
}
?>
