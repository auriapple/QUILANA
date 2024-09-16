<!DOCTYPE html>
<html>
<head>
    <title>Administer Assessment</title>
</head>
<body>
    <?php
    include('db_connect.php');

    // Ensure correct content type for HTML output
    header('Content-Type: text/html');

    // Check if the POST request contains 'assessment_id' and 'class_id'
    if (isset($_POST['assessment_id']) && isset($_POST['class_id'])) {
        // Escape and sanitize the 'assessment_id' and 'class_id'
        $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
        $class_id = $conn->real_escape_string($_POST['class_id']);

        // SQL query to fetch assessment details
        $qry1 = "
            SELECT a.*, aa.*, c.class_name 
            FROM administer_assessment aa
            JOIN assessment a ON aa.assessment_id = a.assessment_id
            JOIN class c ON aa.class_id = c.class_id
            WHERE aa.assessment_id = ? AND aa.class_id = ?
        ";

        // Prepare the statement
        if ($stmt = $conn->prepare($qry1)) {
            // Bind parameters
            $stmt->bind_param("ii", $assessment_id, $class_id);
            
            // Execute the query
            $stmt->execute();
            
            // Get the result
            $result1 = $stmt->get_result();

            // Check if there are any results
            if ($result1->num_rows > 0) {
                $administer = $result1->fetch_assoc();
                ?>
                <h2>Assessment Name: <?php echo htmlspecialchars($administer['assessment_name']); ?> </h2>
                <p>Mode: <?php echo htmlspecialchars($administer['assessment_mode']); ?> </p>
                <p>Time Limit: <?php echo htmlspecialchars($administer['time_limit']); ?> </p>
                <p>Class: <?php echo htmlspecialchars($administer['class_name']); ?> </p>
                <p>Subject: <?php echo htmlspecialchars($administer['subject']); ?> </p>
                <?php
                $administer_id = htmlspecialchars($administer['assessment_id']);

                $qry2 = "
                    SELECT ja.*, aa.administer_id
                    FROM join_assessment ja
                    JOIN administer_assessment aa ON ja.administer_id = aa.administer_id
                    WHERE aa.administer_id = ?
                ";

                // Prepare and execute the second query
                if ($stmt2 = $conn->prepare($qry2)) {
                    $stmt2->bind_param("i", $administer_id);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();

                    // Check if there are results for the second query
                    if ($result2->num_rows > 0) {
                        // Process results from the second query if needed
                        // For now, we just print the number of rows found
                        echo '<p>Number of join assessments: ' . $result2->num_rows . '</p>';
                    } else {
                        echo '<p>No join assessments found.</p>';
                    }

                    // Close the second statement
                    $stmt2->close();
                } else {
                    echo '<div class="alert alert-danger">Failed to prepare the second SQL statement.</div>';
                }
            } else {
                echo '<div class="alert alert-info">No assessments found for this criteria.</div>';
            }

            // Close the first statement
            $stmt->close();
        } else {
            echo '<div class="alert alert-danger">Failed to prepare the SQL statement.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Assessment ID or Class ID is missing.</div>';
    }

    // Close the database connection
    $conn->close();
    ?>
</body>
</html>
