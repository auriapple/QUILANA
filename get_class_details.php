<style>
    .swal2-actions {
        gap: 10px;
    }
    .swal2-actions :is(.secondary-button, .tertiary-button) {
        width: 150px;
        margin-top: 20px;
    }
    #swal2-input {
        width: 340px;
    }
</style>

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
        SELECT a.assessment_id, a.assessment_name, aa.date_administered, aa.administer_id, c.class_name,
            CASE 
                WHEN a.assessment_mode IN (1, 2) THEN SUM(q.total_points)
                WHEN a.assessment_mode = 3 THEN COUNT(q.question_id) * a.max_points
                ELSE 0
            END AS total_points
        FROM administer_assessment aa
        JOIN assessment a ON aa.assessment_id = a.assessment_id
        JOIN questions q ON q.assessment_id = a.assessment_id
        JOIN class c ON aa.class_id = c.class_id
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
        ORDER BY se.status ASC, student_name ASC
    ");

    if (!$qry_student) {
        die("Error: " . $conn->error);
    }
}
?>

<!-- Tab navigation -->
<div style="display: none;" id="class-id-container"><?php echo $class_id; ?></div>
<div class="tabs-container">
    <ul class="tabs">
        <li class="tab-link active" onclick="openTab(event, 'Assessments')">Assessments</li>
        <li class="tab-link" onclick="openTab(event, 'Students')">Students</li>
        <li class="tab-link" id="studentScoresTab" style="display: none;" onclick="openTab(event, 'StudentScores')">Scores</li>
    </ul>
</div>

<!-- Tab content for Assessments -->
<div id="Assessments" class="tabcontent">
<div class="table-wrapper">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Assessment Name</th>
                    <th>Date Administered</th>
                    <th>Total Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Assessments will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Tab content for Students -->
<div id="Students" class="tabcontent" style="display: none;">
    <div class="table-wrapper">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>Student Name</th>
                    <th class="status">Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Student Record will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Tab content for Student Scores -->
<div id="StudentScores" class="tabcontent" style="display: none;">
    <div class="table-wrapper">
        <h6 id="studentScoresTitle"></h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Assessment Name</th>
                    <th>Score</th>
                    <th>Total Score</th>
                    <th>Date Taken</th>
                </tr>
            </thead>
            <tbody id="studentScoresBody">
                <!-- Scores will be dynamically populated here -->
            </tbody>
        </table>
    </div>
</div>

<script>
    let refreshStudentTableInterval;
    var classId = document.getElementById('class-id-container').textContent;

    function startAutoRefresh(classId) {
        if (refreshStudentTableInterval) {
            clearInterval(refreshStudentTableInterval);
        }

        refreshStudentTableInterval = setInterval(function() {
            refreshStudentTable(classId);
        }, 5000);
    }

    function refreshStudentTable(classId) {
        $.ajax({
            url: 'get_students.php', 
            type: 'POST',
            data: { class_id: classId },
            success: function(response) {
                // Update the table's <tbody> with the new HTML
                $('#Students tbody').html('');
                $('#Students tbody').html(response);
                console.log(classId);
            },
            error: function() {
                alert('Failed to refresh the student table.');
            }
        });
    }

    function refreshAssessmentTable(classId) {
        $.ajax({
            url: 'get_class_assessments.php', 
            type: 'POST',
            data: { class_id: classId },
            success: function(response) {
                // Update the table's <tbody> with the new HTML
                $('#Assessments tbody').html('');
                $('#Assessments tbody').html(response);
            },
            error: function() {
                alert('Failed to refresh the assessments table.');
            }
        });

        console.log(classId);
    }

    $(document).ready(function() {
        refreshAssessmentTable(classId);
    });

    function addChildAlert(studentName, className, res) {
        // Create a new div element
        const alert = document.createElement('div');
        alert.className = 'alert-card';
        alert.textContent = studentName + ' has been ' + res + ' ' + className;

        // Append the new child to the parent
        const alertContainer = document.getElementById('alert-container');
        alertContainer.appendChild(alert);

        // Store a reference to the child element
        const thisAlert = alert;

        // Set a timeout to fade out the child after 5 seconds then removed
        setTimeout(() => {
            thisAlert.classList.add('fade');
        }, 5000);
        setTimeout(() => {
            alertContainer.removeChild(thisAlert);
        }, 7000);
    }

    function acceptRejectStudent(classId, studentId, status, classSub, studentName, reason) {
        var res = status == 1 ? 'accepted to ' : status == 2 ? 'rejected from ' : null;

        $.ajax({
            url: 'status_update.php',
            type: 'POST',
            data: {
                class_id: classId,
                student_id: studentId,
                status: status,
                reason: reason
            },
            success: function(response) {
                if (response == 'success') {
                    addChildElement(studentName, classSub, res);
                    console.log(studentName + '\n' + classSub + '\n' + res);
                    fetchPendingRequests();
                } else {
                    Swal.fire({
                        title: 'Warning!',
                        text: 'An error occured in trying to reject/accept ' + studentName + ' to ' + classSub + '.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'popup-content',
                            confirmButton: 'secondary-button'
                        }
                    }).then(() => {
                        warningTracker = false;
                    });
                }
            } 
        });
    }

    // Pending requests buttons functionality
    $(document).on('click', '.accept-btn, .reject-btn', function() {
        var classId = $(this).data('class-id');
        var studentId = $(this).data('student-id');
        var status = $(this).data('status');
        var classSub = $(this).data('class-sub');
        var studentName = $(this).data('student-name');
        var reason;

        if ($(this).hasClass('accept-btn')) {
            acceptRejectStudent(classId, studentId, status, classSub, studentName, reason);
        } else {
            Swal.fire({
                title: 'Confirm Rejection.',
                text: 'Are you sure you want to reject ' + studentName + ' from ' + classSub + '.',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                input: 'text',
                inputPlaceholder: 'Enter reason for rejection',
                customClass: {
                    popup: 'popup-content',
                    confirmButton: 'secondary-button',
                    cancelButton: 'tertiary-button',
                    input: 'popup-input'
                },
                preConfirm: (inputValue) => {
                    if (!inputValue) {
                        Swal.showValidationMessage('Please enter a reason for rejection');
                    }
                    return inputValue;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    reason = result.value;
                    acceptRejectStudent(classId, studentId, status, classSub, studentName, reason)
                    warningTracker = false;
                } else if (result.isDismissed) {
                    console.log("User canceled the removal action.");
                }
            });
        }
    });
 
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        var classId = document.getElementById('class-id-container').textContent;
        
        // Hide all tab contents
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            tabcontent[i].classList.remove("active");
        }
        
        // Remove the 'active' class from all tab links
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }

        // Display the selected tab content
        document.getElementById(tabName).style.display = "block";
        document.getElementById(tabName).classList.add("active");
        
        // Add the 'active' class to the clicked tab link
        evt.currentTarget.classList.add("active");

        // Hide the 'Scores' tab if another tab is clicked
        if (tabName == 'Assessments') {
            refreshAssessmentTable(classId);
        }
        if (tabName !== 'StudentScores') {
            document.getElementById('studentScoresTab').style.display = 'none';
        }
        if (tabName == 'Students') {
            refreshStudentTable(classId);
            startAutoRefresh(classId);
        }
    }

    function showStudentScores(studentId, studentName) {
        fetch(`get_student_scores.php?student_id=${studentId}&class_id=<?php echo $class_id; ?>`)
            .then(response => response.json())
            .then(data => {
                const scoresContainer = document.getElementById('StudentScores');
                const scoresTitle = document.getElementById('studentScoresTitle');
                const scoresBody = document.getElementById('studentScoresBody');
                
                scoresTitle.textContent = ` ${studentName}`;
                scoresBody.innerHTML = ''; 
                
                // Check if data has scores
                if (data.length > 0) {
                    data.forEach(score => {
                        scoresBody.innerHTML += `
                            <tr>
                                <td>${score.assessment_name}</td>
                                <td>${score.score}</td>
                                <td>${score.total_score}</td>
                                <td>${score.date_updated}</td>
                            </tr>
                        `;
                    });
                } else {
                    scoresBody.innerHTML = '<tr><td colspan="4" class="text-center">No scores found for this student.</td></tr>';
                }
                
                document.getElementById('studentScoresTab').style.display = 'block'; 
                openTab({currentTarget: document.getElementById('studentScoresTab')}, 'StudentScores');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching student scores.');
            });
    }

    $('.popup-close').on('click', function() {
        clearInterval(refreshStudentTableInterval);
    });

    function removeAdministeredAssessment(assessmentId, classId, administerId, assessmentName, className) {
        var res = ' removed from ';

        Swal.fire({
            title: 'Confirm Removal',
            text: 'Are you sure you want to remove ' + assessmentName + ' from ' + className + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Remove',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            customClass: {
                popup: 'popup-content',
                confirmButton: 'secondary-button',
                cancelButton: 'tertiary-button'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('remove_administered_assessment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'assessment_id=' + assessmentId + 
                        '&class_id=' + classId + 
                        '&administer_id=' + administerId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        addChildAlert(assessmentName, className, res);
                        refreshAssessmentTable(classId);
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: "Failed to remove administered assessment: " + data.message,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                                confirmButton: 'secondary-button'
                            }
                        }).then((result) => {

                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while removing the administered assessment',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'popup-content',
                            confirmButton: 'secondary-button'
                        }
                    }).then((result) => {

                    });
                });
            } else if (result.isDismissed) {
                console.log("User canceled the removal action.");
            }
        });
    }

    function confirmStudentRemoval(studentId, classId, studentName, className, subject) {
        var res = ' has been removed from ';
        var classSub = className + ' (' + subject + ')';
        console.log(classSub + '\n' + studentName + '\n' + res);

        Swal.fire({
            title: 'Confirm Removal',
            text: 'Are you sure you want to remove ' + studentName + ' from ' + className + ' (' + subject + ')?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Remove',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            customClass: {
                popup: 'popup-content',
                confirmButton: 'secondary-button',
                cancelButton: 'tertiary-button'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('remove_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'student_id=' + studentId + '&class_id=' + classId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        addChildAlert(studentName, classSub, res);
                        refreshStudentTable(<?php echo $class_id ?>)
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: "Failed to remove student: " + data.message,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'popup-content',
                                confirmButton: 'secondary-button'
                            }
                        }).then((result) => {

                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred while removing the student.");
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while removing the student.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'popup-content',
                            confirmButton: 'secondary-button'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload(); 
                        }
                    });
                });
            } else if (result.isDismissed) {
                console.log("User canceled the removal action.");
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var classId = document.getElementById('class-id-container').textContent;
        var defaultTab = document.querySelector('.tab-link.active');

        if (defaultTab) {
            var defaultTabName = defaultTab.getAttribute('onclick').match(/'(.*?)'/)[1];
            document.getElementById(defaultTabName).style.display = "block";
            document.getElementById(defaultTabName).classList.add("active");
        }
    });
</script>

<?php $conn->close(); ?>
