<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use Composer's autoload
require 'vendor/autoload.php';

header('Content-Type: application/json');

include('db_connect.php');

// Get assessment ID and class ID from the request
$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

// Validate input
if ($assessment_id <= 0 || $class_id <= 0) {
    die(json_encode(['error' => 'Invalid or missing assessment_id or class_id']));
}

// Fetch assessment details
$assessment_name_query = $conn->query("
    SELECT assessment_name, assessment_mode, topic 
    FROM assessment 
    WHERE assessment_id = '$assessment_id'
");
$assessment_row = $assessment_name_query->fetch_assoc();
$assessment_name = $assessment_row['assessment_name'];
$assessment_mode = $assessment_row['assessment_mode'];
$assessment_topic = $assessment_row['topic'];

// Assign assessment mode name
if ($assessment_mode == 1) {
    $assessment_mode_name = '(Normal Mode)';
} elseif ($assessment_mode == 2) {
    $assessment_mode_name = '(Quiz Bee Mode)';
} elseif ($assessment_mode == 3) {
    $assessment_mode_name = '(Speed Mode)';
}

// Fetch class name
$class_name_query = $conn->query("
    SELECT class_name 
    FROM class 
    WHERE class_id = '$class_id'
");
$class_row = $class_name_query->fetch_assoc();
$class_name = $class_row['class_name'];

// Prepare the SQL query to fetch scores
$scores_query = "
    SELECT s.student_id, s.firstname, s.lastname, s.student_number, 
        sr.score, sr.total_score, sr.remarks, sr.rank
    FROM student_enrollment se
    JOIN student s ON se.student_id = s.student_id
    LEFT JOIN student_results sr ON s.student_id = sr.student_id 
        AND sr.assessment_id = ?
    WHERE se.class_id = ? AND se.status = 1
    ORDER BY s.lastname ASC, s.firstname ASC";

$stmt = $conn->prepare($scores_query);
if ($stmt === false) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}

// Bind parameters and execute the query
$stmt->bind_param('ii', $assessment_id, $class_id);
if (!$stmt->execute()) {
    die(json_encode(['error' => 'Execute failed: ' . $stmt->error]));
}

// Fetch results
$scores_result = $stmt->get_result();
$scores = [];
while ($row = $scores_result->fetch_assoc()) {
    $scores[] = [
        'student_number' => $row['student_number'],
        'lastname' => $row['lastname'],
        'firstname' => $row['firstname'],
        'score' => isset($row['score']) ? $row['score'] : null,
        'total_score' => isset($row['total_score']) ? $row['total_score'] : null,
        'remarks' => isset($row['remarks']) ? $row['remarks'] : 'N/A',
        'rank' => isset($row['rank']) ? $row['rank'] : 'N/A'
    ];
}

$stmt->close();
$conn->close();

// Create a new Spreadsheet object
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the title of the sheet
$sheet->setTitle($class_name);

// Prepare data for the top rows (header)
$data = [
    [$assessment_name],      // Assessment Name
    [($assessment_mode == 1 ? '(Normal Mode)' : ($assessment_mode == 2 ? '(Quiz Bee Mode)' : '(Speed Mode)'))],  // Assessment Mode
    [$assessment_topic],     // Topic
    ['']                     // Space
];

// Add the header (assessment details)
foreach ($data as $rowIndex => $row) {
    $rowNumber = $rowIndex + 1;
    $sheet->fromArray($row, null, "A$rowNumber");
    
    if ($assessment_mode == 2) {
        $sheet->mergeCells("A$rowNumber:E$rowNumber");
    } else {
        $sheet->mergeCells("A$rowNumber:D$rowNumber");
    }

    $sheet->getStyle("A$rowNumber:E$rowNumber")->getFont()->setBold(true); // Make text bold
    $sheet->getStyle("A$rowNumber:E$rowNumber")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); // Center align
}

// Set the header row
if ($assessment_mode == 1) {
    $headerRow = ['Name', 'Student Number', 'Score', 'Remarks'];
} elseif ($assessment_mode == 2) {
    $headerRow = ['Name', 'Student Number', 'Score', 'Rank', 'Remarks'];
} elseif ($assessment_mode == 3) {
    $headerRow = ['Name', 'Student Number', 'Score', 'Rank'];
}
$sheet->fromArray([$headerRow], null, "A5");

// Set header style to bold
$headerRange = ($assessment_mode == 2) ? 'A5:E5' : 'A5:D5';
$sheet->getStyle($headerRange)->getFont()->setBold(true);

// Prepare the worksheet data
$worksheetData = [];
foreach ($scores as $score) {
    $row = [];
    $row[] = $score['lastname'] . ', ' . $score['firstname'];
    $row[] = $score['student_number'];
    $row[] = $score['score'] !== null ? $score['score'] . ' / ' . $score['total_score'] : 'N/A';

    if ($assessment_mode == 1) {
        $row[] = $score['remarks'];
    } elseif ($assessment_mode == 2) {
        $row[] = $score['rank']; // Add rank column
        $row[] = $score['remarks'];
    } elseif ($assessment_mode == 3) {
        $row[] = $score['rank']; // Add rank in place of remarks
    }

    $worksheetData[] = $row;
}

// Add the worksheet data starting from row 6
$sheet->fromArray($worksheetData, null, "A6");

// Adjust column widths
$sheet->getColumnDimension('A')->setWidth(35); // Name
$sheet->getColumnDimension('B')->setWidth(20); // Student Number

if ($assessment_mode == 1) {
    $sheet->getColumnDimension('C')->setWidth(10); // Score
    $sheet->getColumnDimension('D')->setWidth(10); // Remarks
} elseif ($assessment_mode == 2) {
    $sheet->getColumnDimension('C')->setWidth(10); // Score
    $sheet->getColumnDimension('D')->setWidth(10); // Rank
    $sheet->getColumnDimension('E')->setWidth(15); // Remarks
} elseif ($assessment_mode == 3) {
    $sheet->getColumnDimension('C')->setWidth(10); // Score
    $sheet->getColumnDimension('D')->setWidth(10); // Rank
}

// Center-align specific columns
$sheet->getStyle('B5:B' . (count($worksheetData) + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
if ($assessment_mode == 1) {
    $sheet->getStyle('C5:C' . (count($worksheetData) + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D5:D' . (count($worksheetData) + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
} elseif ($assessment_mode == 2) {
    $sheet->getStyle('C5:C' . (count($worksheetData) + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D5:D' . (count($worksheetData) + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E5:E' . (count($worksheetData) + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
} elseif ($assessment_mode == 3) {
    $sheet->getStyle('C5:C' . (count($worksheetData) + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D5:D' . (count($worksheetData) + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
}

// Highlight entire rows for students that hadn't taken the assessment
$highlightColor = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_YELLOW;
foreach ($worksheetData as $rowIndex => $row) {
    $rowNumber = $rowIndex + 6; // Start from row 6
    if (in_array('N/A', $row)) {
        $endColumn = ($assessment_mode == 2) ? 'E' : 'D';
        $sheet->getStyle("A$rowNumber:$endColumn$rowNumber")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle("A$rowNumber:$endColumn$rowNumber")->getFill()->getStartColor()->setARGB($highlightColor);
    }
}

// Create the file name
$file_name = "{$class_name} ({$assessment_name}) Results.xlsx";

// Save to a temporary file
$temp_file = tempnam(sys_get_temp_dir(), 'report') . '.xlsx';
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($temp_file);

// Check if the file was created successfully
if (!file_exists($temp_file) || filesize($temp_file) === 0) {
    die(json_encode(['error' => 'File creation failed.']));
}

// Set the headers for downloading the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$file_name\"");
readfile($temp_file);

// Clean up the temporary file
unlink($temp_file);
?>