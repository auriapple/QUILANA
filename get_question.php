<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['question_id'])) {
    $question_id = intval($_GET['question_id']);

    // Fetch question details
    $query = "SELECT * FROM questions WHERE question_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $question = $result->fetch_assoc();

        // Map numeric question type to string
        $ques_type_map = [
            1 => 'multiple_choice',
            2 => 'checkbox',
            3 => 'true_false',
            4 => 'identification',
            5 => 'fill_blank'
        ];
        $question_type = $ques_type_map[$question['ques_type']] ?? '';

        $response = [
            'status' => 'success',
            'data' => [
                'question_id' => $question['question_id'],
                'question' => $question['question'],
                'question_type' => $question_type,
                'total_points' => $question['total_points'],
                'options' => [],
                'answer' => ''
            ]
        ];

        // Fetch options or answer based on question type
        switch ($question_type) {
            case 'multiple_choice':
            case 'checkbox':
            case 'true_false':
                $options_query = "SELECT * FROM question_options WHERE question_id = ?";
                $options_stmt = $conn->prepare($options_query);
                $options_stmt->bind_param("i", $question_id);
                $options_stmt->execute();
                $options_result = $options_stmt->get_result();
                while ($option = $options_result->fetch_assoc()) {
                    $response['data']['options'][] = [
                        'option_txt' => $option['option_txt'],
                        'is_right' => $option['is_right']
                    ];
                }
                break;

            case 'identification':
            case 'fill_blank':
                $answer_query = "SELECT identification_answer FROM question_identifications WHERE question_id = ?";
                $answer_stmt = $conn->prepare($answer_query);
                $answer_stmt->bind_param("i", $question_id);
                $answer_stmt->execute();
                $answer_result = $answer_stmt->get_result();
                if ($answer = $answer_result->fetch_assoc()) {
                    $response['data']['answer'] = $answer['identification_answer'];
                }
                break;
        }

        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Question not found.']);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>