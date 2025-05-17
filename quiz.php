<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has already taken the test
$stmt = $pdo->prepare("SELECT has_taken_test FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['has_taken_test']) {
    header("Location: already_taken.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get all questions
    $stmt = $pdo->query("SELECT id, correct_answer FROM questions");
    $questions = $stmt->fetchAll();
    
    $score = 0;
    
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $user_answer = $_POST['question_' . $question_id] ?? '';
        
        if ($user_answer == $question['correct_answer']) {
            $score++;
        }
    }
    
    // Save result
    $pdo->beginTransaction();
    
    try {
        // Insert result
        $stmt = $pdo->prepare("INSERT INTO results (user_id, score) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $score]);
        
        // Update user's has_taken_test status
        $stmt = $pdo->prepare("UPDATE users SET has_taken_test = TRUE WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        
        // Redirect to results page
        $_SESSION['quiz_score'] = $score;
        header("Location: results.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error saving results: " . $e->getMessage());
    }
}

// Get all questions
$stmt = $pdo->query("SELECT * FROM questions");
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Knowledge Quiz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #007bff;
        }
        .quiz-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .question {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .question-text {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .options {
            margin-left: 20px;
        }
        .option {
            margin-bottom: 5px;
        }
        input[type="radio"] {
            margin-right: 10px;
        }
        button {
            display: block;
            width: 200px;
            padding: 10px;
            margin: 20px auto;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .logout-link {
            text-align: right;
            margin-bottom: 20px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
        <h1>General Knowledge Quiz</h1>
        <div class="quiz-info">
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <p>This quiz contains 50 questions. Select the correct answer for each question.</p>
            <p>You need to score at least 15 to pass.</p>
        </div>
        
        <form action="quiz.php" method="post">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question">
                    <div class="question-text">
                        <?php echo ($index + 1) . '. ' . htmlspecialchars($question['question_text']); ?>
                    </div>
                    <div class="options">
                        <div class="option">
                            <input type="radio" name="question_<?php echo $question['id']; ?>" id="q<?php echo $question['id']; ?>_a" value="a" required>
                            <label for="q<?php echo $question['id']; ?>_a">A. <?php echo htmlspecialchars($question['option_a']); ?></label>
                        </div>
                        <div class="option">
                            <input type="radio" name="question_<?php echo $question['id']; ?>" id="q<?php echo $question['id']; ?>_b" value="b">
                            <label for="q<?php echo $question['id']; ?>_b">B. <?php echo htmlspecialchars($question['option_b']); ?></label>
                        </div>
                        <div class="option">
                            <input type="radio" name="question_<?php echo $question['id']; ?>" id="q<?php echo $question['id']; ?>_c" value="c">
                            <label for="q<?php echo $question['id']; ?>_c">C. <?php echo htmlspecialchars($question['option_c']); ?></label>
                        </div>
                        <div class="option">
                            <input type="radio" name="question_<?php echo $question['id']; ?>" id="q<?php echo $question['id']; ?>_d" value="d">
                            <label for="q<?php echo $question['id']; ?>_d">D. <?php echo htmlspecialchars($question['option_d']); ?></label>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <button type="submit">Submit Quiz</button>
        </form>
    </div>
</body>
</html>
