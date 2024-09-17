<!DOCTYPE html>
<html>
<head>
    <title>Administer Assessment</title>
    <style>
        .main-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: auto;
            overflow: hidden;
        }
        
        .top-container {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-content: center;
        }

        .top-container .top-left-container {
            display: flex;
            justify-content: space-between;
            align-content: center;
            flex-direction: column;
            width: 100%;
        }

        .top-container .top-right-container {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            align-items: center;
            width: 100%;
        }

        .top-left-container h1 {
            width: fit-content;
            height: 40px;
            font-weight: 600;
            color: #000000;
            font-size: 32px;
            overflow: hidden;
            white-space: norwap;
        }

        .top-left-container h2 {
            width: fit-content;
            height: 20px;
            font-weight: 400;
            color: #00000080;
            font-size: 15px;
            overflow: hidden;
            white-space: nowrap;
        }

        .top-container h3 {
            width: fit-content;
            height: 38px;
            font-weight: 600;
            color: #1e1a43;
            font-size: 32px;
            overflow: hidden;
            white-space: nowrap;
        }

        .top-right-container h4 {
            width: fit-content;
            font-weight: 400;
            color: #787878;
            font-size: 16px;
            text-align: center;
        }

        .top-right-container button {
            width: 150px;
            padding: 5px;
        }

        .main-container .table-wrapper {
            flex: 1; /* Allow the wrapper to grow and fill available space */
            overflow: hidden; /* Ensure no overflow issues */
            margin: 10px 50px;
            border-radius: 20px;
        }

        .table-wrapper table {
            width: calc(100% - 100px);
            height: 50vh;
            table-layout: fixed;
            margin: 10px 50px;
            overflow: hidden;
            border-radius: 20px;
            justify-self: center;
        }

        .table-wrapper thead, 
        .table-wrapper tr {
            width: 100%;
            padding: 8px;
            text-align: center;
            background-color: #f2f2f2;
            border-radius: 20px;
        }

        .table-wrapper tr {
            height: 20px;
            background-color: #ffffff;
            border-radius: 0px;
        }

        .table-wrapper tbody {
            max-height: calc(50vh - 24px);
            overflow-y: auto;
        }

        .table-wrapper th, 
        .table-wrapper td {
            width: calc(100% / 3);
            text-align: center;
            border-bottom: 1px solid #a494bc;
            border-right: 2px solid #a494bc;
            justify-content: center;
        }

        .table-wrapper td:last-child,
        .table-wrapper th:last-child {
            border-right: none;
        }

        .table-wrapper .joined {
            background-color: #a3a3a338;
            width: 150px;
            height: auto;
            padding: 8px 0px;
            border-radius: 5px;
            display: inline-block;
            justify-content: center;
            align-items: center;
            color: 8a8a8a
        }
    </style>
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
                
                    <div class='table-wrapper'>
                        <table id="dataTable">
                            <thead>
                                <tr>
                                    <th>Student Number</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table data will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                          
                    <script>
                        const assessmentId = <?php echo json_encode($assessment_id); ?>;
                        const classId = <?php echo json_encode($class_id); ?>;

                        function updateTable() {
                            fetch('get_joined.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: new URLSearchParams({
                                    assessment_id: assessmentId,
                                    class_id: classId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                const tbody = document.querySelector('#dataTable tbody');
                                tbody.innerHTML = ''; // Clear existing data

                                if (Array.isArray(data) && data.length > 0) {
                                    data.forEach(item => {
                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                            <td>${item.student_number}</td>
                                            <td>${item.student_name}</td>
                                        `;

                                        // Conditionally add a <div> based on the status value
                                        if (parseInt(item.status) === 0) {
                                            const statusCell = document.createElement('td');
                                            statusCell.innerHTML = '<div class="joined">Joined</div>';
                                            row.appendChild(statusCell);
                                        } else {
                                            // Append an empty cell if the status is not 0
                                            row.innerHTML += '<td> No Status</td>';
                                        }

                                        tbody.appendChild(row);
                                    });
                                } else if (data.error) {
                                    const row = document.createElement('tr');
                                    row.innerHTML = `<td colspan="3" style="text-align: center;">${data.error}</td>`;
                                    tbody.appendChild(row);
                                } else {
                                    const row = document.createElement('tr');
                                    row.innerHTML = `<td colspan="3" style="text-align: center;">No join assessments found</td>`;
                                    tbody.appendChild(row);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        }

                        // Set interval to check for updates every 30 seconds
                        setInterval(updateTable, 3000);

                        // Initial table load
                        updateTable();
                    </script>
                </div> <?php
            } else {
                echo '<div class="alert alert-info">No assessments found for this criteria.</div>';
            }

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
