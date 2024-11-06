<!DOCTYPE html>
<html>
<head>
    <title>Administer Assessment</title>
    <style>
        .content-wrapper,
        .scrollable-content {
            max-height: none;
            height: calc(100vh - 150px);
        }

        .tab-content,
        #administer-container {
            height: 100%;
        }

        .main-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
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
            height: fit-content;
        }

        .top-container .top-right-container {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            align-items: center;
            height: 75px;
            width: 100%;
        }

        .top-left-container h1 {
            margin-bottom: 0;
            width: fit-content;
            height: fit-content;
            font-weight: 600;
            color: #000000;
            font-size: 32px;
        }

        .top-left-container h2 {
            margin-bottom: 0;
            width: fit-content;
            height: 20px;
            font-weight: 400;
            color: #00000080;
            font-size: 15px;
            overflow: hidden;
            white-space: nowrap;
        }

        .top-container h3 {
            margin-bottom: 0;
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
            min-width: fit-content;
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
            height: calc(100% - 70px);
            margin: 10px 50px;
            transition: ease-in 150ms;
        }

        .table-wrapper table {
            width: 100%;
            height: calc(100% - 70px);
            table-layout: fixed;
            overflow: hidden;
            border-radius: 20px;
            border-collapse: separate;
            border: 2px solid rgba(59, 39, 110, 0.80);
            border-spacing: 0;
        }
        
        .table-wrapper thead, 
        .table-wrapper tr {
            width: 100%;
            text-align: center;
            background-color: #f2f2f2;
            border-radius: 20px;
        }

        .table-wrapper thead th {
            background-color: #E0E0EC;
            font-size: 16px;
        }

        .table-wrapper tr {
            display: table;
            height: 20px;
            background-color: #ffffff;
            border-radius: 0px;
            box-sizing: border-box;
        }

        #rowCount {
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        .table-wrapper tbody {
            display: block;
            height: 100%;
            overflow-y: auto;
        }

        .table-wrapper th, 
        .table-wrapper td {
            width: calc(100% / 5);
            text-align: center;
            border-bottom: 1px solid rgba(59, 39, 110, 0.80);
            border-right: 1px solid rgba(59, 39, 110, 0.80);
            justify-content: center;
            padding: 12px;
            color:#4a4a4a;
        }

        .table-wrapper td:last-child,
        .table-wrapper th:last-child {
            border-right: none;
        }

        .table-wrapper tr:last-child {
            border-bottom: none;
        }

        .table-wrapper .joined,
        .table-wrapper .answering,
        .table-wrapper .finished {
            background-color: #a3a3a338;
            width: 150px;
            height: auto;
            padding: 3px 0px;
            border-radius: 5px;
            display: inline-block;
            justify-content: center;
            align-items: center;
            color: #8a8a8a;
            transition: ease-in 300ms;
        }

        .table-wrapper .answering {
            background-color: #fef86e38;
            color: #a7a701;
        }

        .table-wrapper .finished {
            background-color: #00b4d838;
            color: #0077B6;
        }

        .table-wrapper tbody::-webkit-scrollbar {
            display: none;
        }
        #startAssessment,
        #closeAssessment {
            outline: none;
            border-radius: 5px;
        }

        .notification-container {
            display: flex;
            flex-direction: column-reverse;
            padding: 0 5px 5px 0;
            gap: 10px;
            position: absolute;
            bottom: 20px;
            right: 20px;
            height: 300px;
            width: 300px;
            background-color: transparent;
            overflow-y: auto;
        }

        .notification-card {
            position: relative;
            width: 100%;
            height: fit-content;
            border-radius: 10px;
            padding: 15px;
            background-color: #6e72c1dd;
            font-size: 14px;
            color: #fff;
            text-align: justify;
        }

        .notification-card span.notif-close {
            width: 18px;
            height: 18px;
            text-align: center;
            line-height: 18px;
            border-radius: 9px;
            position: absolute;
            top: 7px;
            right: 7px;
            cursor: pointer;
            font-size: 16px;
            color: #fff;
        }

        .notification-card span.notif-close:hover {
            position: absolute;
            top: 7px;
            right: 7px;
            cursor: pointer;
            color: #888;
            background-color: #eee;
        }

        .notification-card .timeStamp {
            position: absolute;
            bottom: 7px;
            right: 15px;
            text-align: right;
            font-size: 10px;
            letter-spacing: 1px;
            color: #999;
        }

        @media screen and (max-width: 1020px) {
            .main-container .table-wrapper {
                margin: 10px 0;
            }

            .scrollable-content::-webkit-scrollbar {
                display: none !important;
            }
        }

        @media screen and (max-width: 920px) {
            .studentNumber-column {
                transition: ease-out 300ms;
                display: none;
            }

            .table-wrapper th, 
            .table-wrapper td {
                width: calc(100% / 4);
                min-width: 93px;
            }

            .table-wrapper .joined,
            .table-wrapper .answering,
            .table-wrapper .finished {
                width: 100px;
            }
        }

        @media screen and (max-width: 720px) {
            .main-container .table-wrapper {
                margin: 0;
            }

            .table-wrapper .joined,
            .table-wrapper .answering,
            .table-wrapper .finished {
                width: 30px;
                border-radius: 15px;
                color: transparent;
            }

            .top-container {
                flex-direction: column;
            }

            .top-container .top-right-container {
                height: 50px;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <?php
    include('db_connect.php');

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
                            <h3><?php echo htmlspecialchars($administer['class_name']) . ' (' . htmlspecialchars($administer['subject']) . ')'?> </h3>
                        </div>

                        <div class='top-right-container'>
                            <?php
                                if ($administer['time_limit'] != null) { ?>
                                    <h4 class='time'>Time Limit: 
                                        <a id="minuteDisplay"> <?php echo htmlspecialchars($administer['time_limit']) ?> </a> 
                                        <a> : </a>
                                        <a id="secondDisplay"> 00 </a>
                                    </h4>
                                <?php }
                            ?>
                            <button id="startAssessment" class='main-button button'
                                style="width: 180px;"
                                data-status = 1
                                data-time="<?php echo htmlspecialchars($administer['time_limit']) ?>">
                                Start
                            </button>
                            <button id="closeAssessment" class="main-button button" style="width: 180px; display: none;"> Close </button>
                        </div>
                        <input type="hidden" id="administerId-container" value="<?php echo $administer['administer_id']; ?>" />
                        <input type="hidden" id="startTime-container" value="<?php echo $administer['start_time']; ?>" />
                    </div>

                    <div class='table-wrapper'>
                        <div id="rowCount">Rows: 0</div>
                        <table id="dataTable">
                            <thead>
                                <tr>
                                    <th class="studentNumber-column">Student Number</th>
                                    <th>Student Name</th>
                                    <th>Number of Suspicious Activities</th>
                                    <th class="status-column">Status</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table data will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                        
                    <div class="notification-container" id="notification-container">
                        <!-- Notifications for suspicious acts will be displayed here -->
                    </div>
                </div>
                 <?php
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

    <script>
        var assessmentId = <?php echo json_encode($assessment_id); ?>;
        var classId = <?php echo json_encode($class_id); ?>;

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
                            <td class="studentNumber-column">${item.student_number}</td>
                            <td>${item.student_name}</td>
                            <td>${item.suspicious_act}</td>
                        `;

                        // Conditionally add a <div> based on the status value
                        if (parseInt(item.status) === 0) {
                            row.innerHTML += '<td class="status-column"> <div class="joined">Joined</div>  </td>';
                        } else if (parseInt(item.status) === 1) {
                            row.innerHTML += '<td class="status-column"> <div class="answering">Answering</div> </td>';
                        } else if (parseInt(item.status) === 2) {
                            row.innerHTML += '<td class="status-column"> <div class="finished">Finished</div> </td>';
                        } else {
                            // Append an empty cell if the status is not 0
                            row.innerHTML += '<td class="status-column">No Status</td>';
                        }

                        if (item.score !== null && item.score !== undefined) {
                            row.innerHTML += `<td>${item.score}/${item.total_score}</td>`;
                        } else {
                            row.innerHTML += '<td>N/A</td>';
                        }
                            

                        tbody.appendChild(row);
                        // Update row count
                        document.getElementById('rowCount').innerText = `Number of Students: ${tbody.rows.length}`;
                    });
                } else if (data.error) {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="5" style="text-align: center;">${data.error}</td>`;
                    tbody.appendChild(row);
                    document.getElementById('rowCount').innerText = `Number of Students: 0`;
                } else {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="5" style="text-align: center;">No Students have joined</td>`;
                    tbody.appendChild(row);
                    document.getElementById('rowCount').innerText = `Number of Students: 0`;
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Set interval to check for updates every 3 seconds
        setInterval(updateTable, 3000);

        // Initial table load
        updateTable();

        function updateNotifs() {
            $.ajax({
                url: 'switchTab_notifs.php',
                method: 'POST',
                data: { class_id: classId, assessment_id: assessmentId },
                success: function(response) {
                    $('#notification-container').html(response);
                    addCloseListeners();
                }
            });
        }

        let updateNotifsInterval = setInterval(updateNotifs, 3000);

        // Function to add click listeners to all close buttons
        function addCloseListeners() {
            document.querySelectorAll('.notif-close').forEach(closeButton => {
                closeButton.addEventListener('click', function() {
                    // Get the notification card and retrieve the necessary data
                    var notificationCard = this.parentElement;
                    var administerId = notificationCard.getAttribute('data-administer-id');
                    var studentId = notificationCard.getAttribute('data-student-id');

                    // Hide the notification card visually
                    notificationCard.style.display = 'none';

                    // Send an AJAX request to update the if_display attribute in the database
                    fetch('switchTab_displayUpdate.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            administer_id: administerId,
                            student_id: studentId,
                            if_display: false
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.error('Failed to update the database:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        }

        function updateStatus(administerId, status) {
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ administer_id: administerId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (status == 1) {
                        alert('Assessment started successfully!');
                    } else if (status == 2) {
                        alert('Assessment stopped successfully!');
                    }
                } else {
                    alert('Failed to update assessment status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        let interval;
        let ifStopAssessmentInterval;

        document.getElementById('startAssessment').addEventListener('click', function() {
            $('#startAssessment').hide();
            const administerId = document.getElementById('administerId-container').value;

            console.log('Starting assessment for administerId:', administerId);

            fetch('store_startTime.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ administer_id: administerId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Start time updated:', data.start_time);
                    sessionStorage.setItem(`start_time_${administerId}`, data.start_time);
                    initializeTimer(data.start_time);
                    updateStatus(administerId, 1);
                } else {
                    alert('Failed to start the assessment: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        function checkTimeStarted() {
            const administerId = document.getElementById('administerId-container').value;
            const storedStartTime = sessionStorage.getItem(`start_time_${administerId}`);

            if (storedStartTime) {
                $('#startAssessment').hide();
                initializeTimer(storedStartTime);
            } else {
                const startTime = document.getElementById('startTime-container').value;

                if(startTime) {
                    $('#startAssessment').hide();
                    sessionStorage.setItem(`start_time_${administerId}`, startTime);
                    initializeTimer(startTime);
                }
            }
        }

        checkTimeStarted();

        // Function to initialize the timer
        function initializeTimer(startTime) {
            const timeLimit = parseInt(document.getElementById('startAssessment').getAttribute('data-time'), 10);
            const countdownTime = timeLimit * 60;

            const startTimeDate = new Date(startTime + ' GMT+0800');
            
            interval = setInterval(function() {
                const now = new Date();
                const elapsedTime = Math.floor((now - startTimeDate) / 1000);
                const remainingTime = countdownTime - elapsedTime;

                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;

                // Update the timer display
                const minuteDisplay = document.getElementById('minuteDisplay');
                const secondDisplay = document.getElementById('secondDisplay');

                minuteDisplay.textContent = minutes < 10 ? '0' + minutes : minutes;
                secondDisplay.textContent = seconds < 10 ? '0' + seconds : seconds;

                // Check if time is up
                if (remainingTime <= 0) {
                    clearInterval(interval);
                    const administerId = document.getElementById('administerId-container').value;
                    updateStatus(administerId, 2);
                    $('#closeAssessment').show();
                }
            }, 1000);
        }

        function ifStopAssessment() {
            var administerId = document.getElementById('administerId-container').value;

            fetch('if_done.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({administer_id: administerId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {   
                    clearInterval(interval);      
                    clearInterval(ifStopAssessmentInterval);
                    clearInterval(updateNotifs); 
                    updateStatus(administerId, 2);
                    $('#closeAssessment').show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        ifStopAssessmentInterval = setInterval(ifStopAssessment, 3000);

        $('#closeAssessment').on('click', function() {
            window.location.href = 'assessment.php';
        });
    </script>
</body>
</html>