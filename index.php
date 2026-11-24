<?php

session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$alertErrors = $_SESSION['errors'] ?? [];
$previousValues = $_SESSION['old'] ?? [];
$alertSuccess = $_SESSION['success'] ?? '';

unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success']);

$courseOptions = [
    'Computer Science',
    'Information Technology',
    'Business Administration',
    'Education',
    'Nursing',
];

$studentQuery = $pdo->query(
    'SELECT
        id,
        full_name,
        email,
        age,
        course,
        registration_date
     FROM students_registration
     ORDER BY registration_date DESC, id DESC'
);
$studentRows = $studentQuery->fetchAll();

$registeredCount = $pdo->query(
    'SELECT COUNT(*) FROM students_registration'
)->fetchColumn();

function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <header>
            <h1>Riverside Technical College</h1>
            <p>Student Registration System</p>
        </header>

        <section class="form-section">
            <h2>Register New Student</h2>

            <?php if ($alertSuccess !== ''): ?>
                <div class="success"><?= escapeHtml($alertSuccess) ?></div>
            <?php endif; ?>

            <?php if (!empty($alertErrors)): ?>
                <div class="error">
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        <?php foreach ($alertErrors as $alertError): ?>
                            <li><?= escapeHtml($alertError) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="process.php" method="post">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
                >

                <label for="full_name">Full Name</label>
                <input
                    id="full_name"
                    type="text"
                    name="full_name"
                    placeholder="Enter your full name"
                    value="<?= escapeHtml($previousValues['full_name'] ?? '') ?>"
                    required
                >

                <label for="email">Email Address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?= escapeHtml($previousValues['email'] ?? '') ?>"
                    required
                >

                <label for="age">Age</label>
                <input
                    id="age"
                    type="number"
                    name="age"
                    min="16"
                    max="100"
                    placeholder="Enter your age"
                    value="<?= escapeHtml($previousValues['age'] ?? '') ?>"
                    required
                >

                <label for="course">Select Course</label>
                <select id="course" name="course" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courseOptions as $courseOption): ?>
                        <option
                            value="<?= escapeHtml($courseOption) ?>"
                            <?= ($previousValues['course'] ?? '') === $courseOption ? 'selected' : '' ?>
                        >
                            <?= escapeHtml($courseOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Register Student</button>
            </form>
        </section>

        <section class="students-section">
            <h2>Registered Students</h2>

            <div class="total">
                Total Registered Students:
                <strong><?= escapeHtml($registeredCount) ?></strong>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Age</th>
                            <th>Course</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($studentRows) === 0): ?>
                            <tr>
                                <td colspan="6">No students registered yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($studentRows as $student): ?>
                                <tr>
                                    <td><?= escapeHtml($student['id']) ?></td>
                                    <td><?= escapeHtml($student['full_name']) ?></td>
                                    <td><?= escapeHtml($student['email']) ?></td>
                                    <td><?= escapeHtml($student['age']) ?></td>
                                    <td><?= escapeHtml($student['course']) ?></td>
                                    <td><?= escapeHtml($student['registration_date']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
