<?php

session_start();
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$formFields = [
    'full_name',
    'email',
    'age',
    'course',
];

$submitted = [];
foreach ($formFields as $field) {
    $submitted[$field] = isset($_POST[$field]) && is_string($_POST[$field])
        ? trim($_POST[$field])
        : '';
}

$submitted['csrf_token'] = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
    ? trim($_POST['csrf_token'])
    : '';

$_SESSION['old'] = [
    'full_name' => $submitted['full_name'],
    'email' => $submitted['email'],
    'age' => $submitted['age'],
    'course' => $submitted['course'],
];

$messages = [];
$availableCourses = [
    'Computer Science',
    'Information Technology',
    'Business Administration',
    'Education',
    'Nursing',
];

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $submitted['csrf_token'])
) {
    $messages[] = 'Invalid or expired form token. Please try again.';
}

if (
    $submitted['full_name'] === '' ||
    $submitted['email'] === '' ||
    $submitted['age'] === '' ||
    $submitted['course'] === ''
) {
    $messages[] = 'All fields are required.';
}

if ($submitted['full_name'] !== '') {
    $nameLength = mb_strlen($submitted['full_name'], 'UTF-8');

    if ($nameLength < 3 || $nameLength > 100) {
        $messages[] = 'Full name must be between 3 and 100 characters.';
    }

    if (!preg_match('/^[\p{L} \'\-]+$/u', $submitted['full_name'])) {
        $messages[] = 'Name can only contain letters, spaces, apostrophes and hyphens.';
    }
}

if (
    $submitted['email'] !== '' &&
    !filter_var($submitted['email'], FILTER_VALIDATE_EMAIL)
) {
    $messages[] = 'Please enter a valid email address.';
}

if (
    $submitted['age'] !== '' &&
    (
        !preg_match('/^\d+$/', $submitted['age']) ||
        (int) $submitted['age'] < 16 ||
        (int) $submitted['age'] > 100
    )
) {
    $messages[] = 'Age must be a whole number between 16 and 100.';
}

if (
    $submitted['course'] !== '' &&
    !in_array($submitted['course'], $availableCourses, true)
) {
    $messages[] = 'Please select a valid course.';
}

if (empty($messages) && $submitted['email'] !== '') {
    $emailLookup = $pdo->prepare(
        'SELECT id
         FROM students_registration
         WHERE LOWER(email) = LOWER(:email)
         LIMIT 1'
    );
    $emailLookup->execute(['email' => $submitted['email']]);

    if ($emailLookup->fetchColumn() !== false) {
        $messages[] = 'This email address is already registered.';
    }
}

if (!empty($messages)) {
    $_SESSION['errors'] = $messages;
    header('Location: index.php');
    exit;
}

try {
    $record = $pdo->prepare(
        'INSERT INTO students_registration
            (full_name, email, age, course)
         VALUES
            (:student_name, :student_email, :student_age, :student_course)'
    );

    $record->execute([
        'student_name' => $submitted['full_name'],
        'student_email' => strtolower($submitted['email']),
        'student_age' => (int) $submitted['age'],
        'student_course' => $submitted['course'],
    ]);

    unset($_SESSION['old']);
    $_SESSION['success'] = 'Student registered successfully!';

    header('Location: index.php');
    exit;
} catch (PDOException $exception) {
    if ((int) $exception->errorInfo[1] === 1062) {
        $_SESSION['errors'] = ['This email address is already registered.'];
        header('Location: index.php');
        exit;
    }

    error_log('Student registration failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Sorry, we could not save the registration. Please try again.');
}
