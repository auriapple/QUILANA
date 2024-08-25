-- Create tables that do not have foreign key dependencies
CREATE TABLE code (
    code_id INT AUTO_INCREMENT PRIMARY KEY,
    generated_code VARCHAR(255) NOT NULL
);

CREATE TABLE faculty (
    faculty_id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(150) NOT NULL,
    lastname VARCHAR(150) NOT NULL,
    faculty_number VARCHAR(15) NOT NULL,
    webmail VARCHAR(150) NOT NULL,
    username VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL, 
    user_type TINYINT(1) DEFAULT 2 NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

CREATE TABLE student (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(150) NOT NULL,
    lastname VARCHAR(150) NOT NULL,
    webmail VARCHAR(150) NOT NULL,
    student_number VARCHAR(15) NOT NULL,
    username VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL, 
    user_type TINYINT(1) DEFAULT 3 NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

CREATE TABLE course (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT,
    course_name VARCHAR(150) NOT NULL,
    faculty_id INT NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
);

CREATE TABLE class (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    code_id INT NOT NULL,
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    student_id INT,
    assessment_id INT,
    year TINYINT NOT NULL,
    section VARCHAR(2) NOT NULL,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (code_id) REFERENCES code(code_id),
    FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id),
    FOREIGN KEY (course_id) REFERENCES course(course_id)
);

CREATE TABLE assessment (
    assessment_id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_mode TINYINT(1) NOT NULL,
    assessment_name VARCHAR(150) NOT NULL,
    course_id INT NOT NULL,
    topic VARCHAR(200) NOT NULL,
    faculty_id INT NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (course_id) REFERENCES course(course_id),
    FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
);

CREATE TABLE administer_assessment (
    administer_id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    course_id INT NOT NULL,
    class_id INT NOT NULL,
    timelimit INT NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES assessment(assessment_id),
    FOREIGN KEY (course_id) REFERENCES course(course_id),
    FOREIGN KEY (class_id) REFERENCES class(class_id)
);


CREATE TABLE student_enrollment (
    studentEnrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    status TINYINT(1) NOT NULL,
    FOREIGN KEY (class_id) REFERENCES class(class_id),
    FOREIGN KEY (student_id) REFERENCES student(student_id)
);

CREATE TABLE student_submission (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    student_id INT NOT NULL,
    student_score INT(11) NOT NULL,
    status TINYINT(1) NOT NULL,
    date_taken DATETIME NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES assessment(assessment_id),
    FOREIGN KEY (student_id) REFERENCES student(student_id)
);

CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    assessment_id INT NOT NULL,
    order_by INT NOT NULL,
    ques_type TINYINT(1) NOT NULL,
    total_points INT(3) NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (assessment_id) REFERENCES assessment(assessment_id)
);

CREATE TABLE question_options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    option_txt TEXT NOT NULL,
    is_right TINYINT(1) NOT NULL,
    question_id INT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);

CREATE TABLE question_identifications (
    identification_id INT AUTO_INCREMENT PRIMARY KEY,
    identification_answer TEXT NOT NULL,
    question_id INT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);

CREATE TABLE student_answer (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    answer_text TEXT NOT NULL,
    identification_id INT,
    submission_id INT NOT NULL,
    question_id INT NOT NULL,
    option_id INT,
    is_right TINYINT(1) NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (submission_id) REFERENCES student_submission(submission_id),
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);

CREATE TABLE student_results (
    results_id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    items INT(3) NOT NULL,
    score INT(3) NOT NULL,
    remarks TINYINT(1),
    rank INT(3),
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (assessment_id) REFERENCES assessment(assessment_id),
    FOREIGN KEY (student_id) REFERENCES student(student_id),
    FOREIGN KEY (class_id) REFERENCES class(class_id)
);

CREATE TABLE flashcard (
    flashcard_id INT AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(255) NOT NULL,
    definition VARCHAR(255) NOT NULL,
    student_id INT NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (student_id) REFERENCES student(student_id)
);

CREATE TABLE rw_questions (
    rw_question_id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    order_by INT NOT NULL,
    question_type TINYINT(1) NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);


CREATE TABLE rw_question_opt (
    rw_option_id INT AUTO_INCREMENT PRIMARY KEY,
    option_text TEXT NOT NULL,
    is_right TINYINT(1) NOT NULL,
    rw_question_id INT NOT NULL,
    FOREIGN KEY (rw_question_id) REFERENCES rw_questions(rw_question_id)
);

CREATE TABLE rw_question_identifications (
    rw_identification_id INT AUTO_INCREMENT PRIMARY KEY,
    rw_question_id INT NOT NULL,
    identification_answer TEXT NOT NULL,
    FOREIGN KEY (rw_question_id) REFERENCES rw_questions(rw_question_id)
);

CREATE TABLE rw_reviewer (
    reviewer_id INT AUTO_INCREMENT PRIMARY KEY,
    sharedcode_int INT,
    student_id INT,
    topic VARCHAR(255) NOT NULL,
    reviewer_type TINYINT(1) NOT NULL,
    rw_question_id INT NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (rw_question_id) REFERENCES rw_questions(rw_question_id)
);

CREATE TABLE shared_code (
    sharedcode_id INT AUTO_INCREMENT PRIMARY KEY,
    flashcard_id INT NOT NULL,
    generated_code VARCHAR(255) NOT NULL,
    reviewer_id INT NOT NULL,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewer_id) REFERENCES rw_reviewer(reviewer_id),
    FOREIGN KEY (flashcard_id) REFERENCES flashcard(flashcard_id)
);

CREATE TABLE get_shared_code (
    getcode_id INT AUTO_INCREMENT PRIMARY KEY,
    sharedcode_id INT NOT NULL,
    is_valid TINYINT(1) NOT NULL,
    student_id INT NOT NULL,
    date_entered DATETIME NOT NULL,
    FOREIGN KEY (sharedcode_id) REFERENCES shared_code(sharedcode_id)
);

CREATE TABLE rw_student_submission (
    rw_submission_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    student_score INT(11) NOT NULL,
    status TINYINT(1) NOT NULL,
    date_taken DATETIME NOT NULL,
    FOREIGN KEY (student_id) REFERENCES student(student_id),
    FOREIGN KEY (reviewer_id) REFERENCES rw_reviewer(reviewer_id)
);

CREATE TABLE rw_answer (
    rw_answer_id INT AUTO_INCREMENT PRIMARY KEY,
    answer_text TEXT NOT NULL,
    rw_submission_id INT NOT NULL,
    rw_question_id INT NOT NULL,
    rw_option_id INT,
    is_right TINYINT(1) NOT NULL,
    date_updated DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (rw_submission_id) REFERENCES rw_student_submission(rw_submission_id),
    FOREIGN KEY (rw_question_id) REFERENCES rw_questions(rw_question_id),
    FOREIGN KEY (rw_option_id) REFERENCES rw_question_opt(rw_option_id)
);