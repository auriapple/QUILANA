<?php
include('db_connect.php');
include('auth.php');

if(isset($_GET['course_id'])){
    $course_id = $_GET['course_id'];
    $qry = $conn->query("SELECT * FROM course WHERE course_id = ".$course_id);
    if($qry->num_rows > 0){
        $course = $qry->fetch_assoc();
        echo json_encode(['status' => 1, 'course' => $course]);
    } else {
        echo json_encode(['status' => 0, 'msg' => 'Course not found']);
    }
    exit;
}
?>
