<!DOCTYPE html>
<html>
<head>
    <title>Administer Assessment</title>
    <style>
        .main-container {
            display: flex;
            flex-direction: column;
        }
        
        .top-container {
            display: flex;
            justify-content: space-between;
            align-content: center;
            outline: 1px solid red;
        }

        .top-container .top-left-container {
            display: flex;
            justify-content: space-between;
            align-content: center;
            flex-direction: column;
            width: 100%;
            outline: 1px solid blue;
        }

        .top-container .top-right-container {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            align-items: center;
            width: 100%;
            outline: 1px solid yellow;
        }

        .top-left-container h1 {
            width: fit-content;
            height: 48px;
            font-weight: 600;
            color: #000000;
            font-size: 40px;
            outline: 1px solid;
        }

        .top-left-container h2 {
            width: fit-content;
            height: 20px;
            font-weight: 400;
            color: #00000080;
            font-size: 15px;
            white-space: nowrap;
            outline: 1px solid;
        }

        .top-container h3 {
            width: fit-content;
            height: 38px;
            font-weight: 600;
            color: #1e1a43;
            font-size: 32px;
            outline: 1px solid;
        }

        .top-right-container .h4 {
            width: fit-content;
            font-weight: 400;
            color: #787878;
            font-size: 16px;
        }

        .top-right-container button {
            width: 150px;
            padding: 5px
        }
    </style>
    <link rel="stylesheet" href="">
</head>
<body>
    <?php
    include('db_connect.php');

    // Ensure correct content type for HTML output
    header('Content-Type: text/html');

    // Check if the POST request contains 'assessment_id' and 'class_id'
    // if (isset($_POST['assessment_id']) && isset($_POST['class_id'])) {
        // Escape and sanitize the 'assessment_id' and 'class_id'
        // $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
        // $class_id = $conn->real_escape_string($_POST['class_id']);

        // SQL query to fetch assessment details
        $qry1 = "
            SELECT a.*, aa.*, c.class_name 
            FROM administer_assessment aa
            JOIN assessment a ON aa.assessment_id = a.assessment_id
            JOIN class c ON aa.class_id = c.class_id
            WHERE aa.assessment_id = '1' AND aa.class_id = '20'
        ";

        // Prepare the statement
        if ($stmt = $conn->prepare($qry1)) {
            // Bind parameters
            // $stmt->bind_param("ii", $assessment_id, $class_id);
            
            // Execute the query
            $stmt->execute();
            
            // Get the result
            $result1 = $stmt->get_result();

            // Check if there are any results
            if ($result1->num_rows > 0) {
                $administer = $result1->fetch_assoc();
                ?>
                <div class='main-container'>
                    <div class='top-container'>
                        <div class='top-left-container'>
                            <h1> <?php echo htmlspecialchars($administer['assessment_name']); ?> </h1>
                            <?php
                                switch ($administer['assessment_mode']) {
                                    case 1: ?>
                                        <h2>Normal Mode</h2> 
                                        <?php break;
                                    case 2: ?>
                                        <h2>Quiz Bee Mode</h2> 
                                        <?php break;
                                    case 3: ?>
                                        <h2>Speed Mode</h2> 
                                        <?php break;
                                    default: ?>
                                        <h2>Mode was not chosen</h2>
                                <?php }
                            ?>
                        </div>

                        <div class='top-right-container'>
                            <?php
                                if ($administer['time_limit'] != null) { ?>
                                    <h4 class='time'>Time Limit: <?php echo htmlspecialchars($administer['time_limit']); ?> </h4>
                                <?php } else { ?>
                                    <h4 class='time'>Time Limit: no time limit set</h4>
                                <?php } 
                            ?>
                            <button class='main-button button'> Start </button>
                        </div>
                    </div>

                    <h3><?php echo htmlspecialchars($administer['class_name']) . ' (' . htmlspecialchars($administer['subject']) . ')'?> </h3>
                </div>
                

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
    /* } else {
        echo '<div class="alert alert-danger">Assessment ID or Class ID is missing.</div>';
    } */

    // Close the database connection
    $conn->close();
    ?>
</body>
</html>
