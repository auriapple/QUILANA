<?php
include('db_connect.php');

if (isset($_POST['assessment_id'])) {
    $assessment_id = $_POST['assessment_id'];
    $qry = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");
    $result = $qry->fetch_assoc();
    echo json_encode($result);
}
?>
