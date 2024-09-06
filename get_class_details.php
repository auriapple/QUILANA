<?php
include('db_connect.php');

if (isset($_GET['class_id'])) {
    $class_id = $conn->real_escape_string($_GET['class_id']);

    // Fetch the class details
    $qry_class = $conn->query("SELECT class_name, subject FROM class WHERE class_id = '$class_id'");
    if ($qry_class->num_rows > 0) {
        $class = $qry_class->fetch_assoc();
        echo "<h4><strong>{$class['class_name']} ({$class['subject']})</strong></h4>";
    } else {
        echo "<p><strong>Class not found.</strong></p>";
    }

    // Fetch the assessments with related details
    $qry_assessments = $conn->query("
        SELECT a.assessment_id, a.assessment_name, aa.date_administered, SUM(q.total_points) AS total_points
        FROM administer_assessment aa
        JOIN assessment a ON aa.assessment_id = a.assessment_id
        JOIN questions q ON q.assessment_id = a.assessment_id
        WHERE aa.class_id = '$class_id'
        GROUP BY a.assessment_id, a.assessment_name, aa.date_administered
    ");

    if (!$qry_assessments) {
        die("Error: " . $conn->error);
    }

    // Fetch the students with concatenated name and enrollment status
    $qry_student = $conn->query("
        SELECT s.student_id, s.student_number, CONCAT(s.lastname, ', ', s.firstname) AS student_name, se.status
        FROM student_enrollment se
        JOIN student s ON se.student_id = s.student_id
        WHERE se.class_id = '$class_id' AND se.status != 2
    ");

    if (!$qry_student) {
        die("Error: " . $conn->error);
    }
    /* WIP
    // Fetch the student's results of the assessments in that class
    $qry_results = $conn->query("
        SELECT * FROM student_results 
        WHERE class_id = '$class_id' AND student_id = '$_POST[student_id]'
    ");

    if (!$qry_results) {
        die("Error: " . $conn->error);
    }
        */
}
?>

<!-- Tab navigation -->
<div class="tabs-container">
    <ul class="tabs">
        <li class="tab-link active" onclick="openTab(event, 'Assessments')">Assessments</li>
        <li class="tab-link" onclick="openTab(event, 'Students')">Students</li>
    </ul>
</div>

<!-- Tab content for Assessments -->
<div id="Assessments" class="tabcontent">
    <?php
    if (isset($qry_assessments)) {
        // Display the table for assessments
        echo '<div class="course-details-table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Assessment Name</th>
                            <th>Date Administered</th>
                            <th>Total Score</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

        if ($qry_assessments->num_rows > 0) {
            while ($assessment = $qry_assessments->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($assessment['assessment_name']) . '</td>
                        <td>' . htmlspecialchars($assessment['date_administered']) . '</td>
                        <td>' . htmlspecialchars($assessment['total_points']) . '</td>
                      <td>
                        <div class="btn-container">
                        <a href="view_assessment.php?id=' . htmlspecialchars($assessment['assessment_id']) . '&class_id=' . htmlspecialchars($class_id) . '" class="btn btn-primary btn-sm">View</a>
                        <button class="btn btn-danger btn-sm" onclick="removeAdministeredAssessment(' . htmlspecialchars($assessment['assessment_id']) . ', ' . htmlspecialchars($class_id) . ')">Remove</button>
                        </div>
                    </td>
                    </tr>';
            }
        } else {
            echo '<tr>
                    <td colspan="4" class="text-center">No assessments found.</td>
                </tr>';
        }

        echo '</tbody></table></div>';
    }
    ?>
</div>

<!-- Tab content for Students -->
<div id="Students" class="tabcontent" style="display: none;">
    <?php
    $class_id = $conn->real_escape_string($_GET['class_id']);

    if (isset($qry_student)) {
        // Display the table for students
        echo '<div class="course-details-table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

        if ($qry_student->num_rows > 0) {
            while ($student = $qry_student->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($student['student_number']) . '</td>
                        <td>' . htmlspecialchars($student['student_name']) . '</td>';
                        if (htmlspecialchars($student['status']) == 0) {
                            echo '<td> Pending </td>';
                            echo '<td>'; 
                            ?> <div>
                                <button class="btn btn-primary btn-sm accept-btn" 
                                        data-class-id="<?php echo $class_id ?>" 
                                        data-student-id="<?php echo $student['student_id'] ?>" 
                                        data-status="1" 
                                        type="button">Accept</button>
                                <button class="btn btn-primary btn-sm reject-btn" 
                                        data-class-id="<?php echo $class_id ?>" 
                                        data-student-id="<?php echo $student['student_id'] ?>" 
                                        data-status="2" 
                                        type="button">Reject</button>
                            </div> 
                            <?php echo '</td>';
                        } else if (htmlspecialchars($student['status']) == 1){
                            echo '<td> Enrolled </td>';
                            echo '<td>' ?><div class="btn-container">
                            <button class="btn btn-primary btn-sm" data-class-id="<?php $class_id ?>"  type="button">Scores</button>
                            <button class="btn btn-primary btn-sm reject-btn" data-class-id="<?php $class_id ?>"  type="button">Remove</button>
                            </div> <?php '</td>';
                        };
                    echo '</tr>';
            }
        } else {
            echo '<tr>
                    <td colspan="4" class="text-center">No students found.</td>
                </tr>';
        }

        echo '</tbody></table></div>';
    }
    ?>
</div>

<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none"; // Hide all tab content
        tabcontent[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    document.getElementById(tabName).style.display = "block"; // Show the selected tab
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

    // Ensure the first tab is shown by default when the page loads
    document.addEventListener('DOMContentLoaded', function() {
    var defaultTab = document.querySelector('.tab-link.active');
    if (defaultTab) {
        var defaultTabName = defaultTab.getAttribute('onclick').match(/'(.*?)'/)[1];
        document.getElementById(defaultTabName).style.display = "block";
        document.getElementById(defaultTabName).classList.add("active");
    }

    // Ensure the first tab is shown by default when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        var defaultTab = document.querySelector('.tab-link.active');
        if (defaultTab) {
            var defaultTabName = defaultTab.getAttribute('onclick').match(/'(.*?)'/)[1];
            document.getElementById(defaultTabName).style.display = "block";
            document.getElementById(defaultTabName).classList.add("active");
        }
    });
});
    function removeAdministeredAssessment(assessmentId, classId) {
    if (confirm("Are you sure you want to remove this administered assessment from this class?")) {
        fetch('remove_administered_assessment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'assessment_id=' + assessmentId + '&class_id=' + classId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Administered assessment removed successfully from this class");
                location.reload(); // Reload the page to reflect the changes
            } else {
                alert("Failed to remove administered assessment: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while removing the administered assessment");
        });
    }
}


    </script>

<?php $conn->close(); ?>
