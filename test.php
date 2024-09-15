<!DOCTYPE html>
<html lang="en">
    <body>
        <?php 
        include('db_connect.php');
        
        echo 'test';

        // if (isset($_POST['administer_id'])) {
            $administer_id = 1; //$conn->real_escape_string($_POST['administer_id']);

            $qry1 = "
                SELECT a.*, aa.*, c.class_name FROM administer_assessment aa
                JOIN assessment a ON aa.assessment_id = a.assessment_id
                JOIN class c ON aa.class_id  = c.class_id
                WHERE aa.administer_id = '$administer_id'
            ";
            
            $result1 = $conn->query($qry1);
            
            if ($result1->num_rows > 0) {
                $assessment = $result1->fetch_assoc();

                echo $assessment['assessment_name']. '<br>';
                echo $assessment['assessment_mode']. '<br>';
                echo $assessment['time_limit']. '<br>';
                echo $assessment['class_name']. '<br>';
                echo $assessment['subject']. '<br>';
            } else {
                echo 'No data found for this administer ID.';
            }
        //} else {
            //echo 'Administer ID is missing.';
        //}
        ?>
    </body>
</html>
