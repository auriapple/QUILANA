<?php
include('db_connect.php');

$class_id = $_POST['class_id'];

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

if ($qry_assessments->num_rows > 0) {
    while ($assessment = $qry_assessments->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($assessment['assessment_name']) . '</td>
                <td>' . htmlspecialchars($assessment['date_administered']) . '</td>
                <td>' . htmlspecialchars($assessment['total_points']) . '</td>
                <td>
                    <div class="btn-container">
                        <a href="view_assessment.php?id=' . htmlspecialchars($assessment['assessment_id']) . '&class_id=' . htmlspecialchars($class_id) . '" class="btn btn-primary btn-sm">View</a>'; ?>
                        <button class="btn btn-danger btn-sm" 
                                onclick="removeAdministeredAssessment(
                                    '<?php echo htmlspecialchars($assessment['assessment_id'], ENT_QUOTES); ?>', 
                                    '<?php echo htmlspecialchars($class_id, ENT_QUOTES); ?>', 
                                    '<?php echo htmlspecialchars($assessment['administer_id'], ENT_QUOTES); ?>', 
                                    '<?php echo htmlspecialchars($assessment['assessment_name'], ENT_QUOTES); ?>', 
                                    '<?php echo htmlspecialchars($assessment['class_name'], ENT_QUOTES); ?>'
                                )">
                            Remove
                        </button> <?php
                    echo '</div>
                </td>
            </tr>';
    }
} else {
    echo '<tr>
        <td colspan="4" class="text-center no-records">No assessments found.</td>
    </tr>';
}
?>