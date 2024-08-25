<?php
include('db_connect.php');
include('auth.php');

if(isset($_POST['course_name'])){
    $course_id = isset($_POST['course_id']) ? $_POST['course_id'] : '';
    $faculty_id = $_POST['faculty_id'];
    $course_name = $_POST['course_name'];

    if(empty($course_id)){
        // Insert new course
        $qry = $conn->query("INSERT INTO course (faculty_id, course_name) VALUES ('$faculty_id', '$course_name')");
    } else {
        // Update existing course
        $qry = $conn->query("UPDATE course SET course_name='$course_name' WHERE id='$course_id' AND faculty_id='$faculty_id'");
    }

    if($qry){
        echo json_encode(['status' => 1]);
    } else {
        echo json_encode(['status' => 0, 'msg' => 'Failed to save course']);
    }
}
?>
