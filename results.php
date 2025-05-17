<?php
session_start();
include 'config.php';

// Check if user is logged in and has a quiz score
if (!isset($_SESSION['user_id']) || !isset($_SESSION['quiz_score'])) {
    header("Location: login.php");
    exit();
}

$score = $_SESSION['quiz_score'];
$passed = $score > 15;

// Clear the quiz score from session
unset($_SESSION['quiz_score']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h1 {
            color: <?php echo $passed ? '#28a745' : '#dc3545'; ?>;
            margin-bottom: 20px;
        }
        .score {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }
        .message {
            margin-bottom: 20px;
            font-size: 18px;
        }
        .passed {
            color: #28a745;
        }
        .failed {
            color: #dc3545;
        }
        .logout-link {
            margin-top: 20px;
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
        <h1><?php echo $passed ? 'Congratulations!' : 'Quiz Results'; ?></h1>
        <div class="score">Your Score: <?php echo $score; ?>/50</div>
        <div class="message <?php echo $passed ? 'passed' : 'failed'; ?>">
            <?php 
            if ($passed) {
                echo "Well done! You passed the quiz with a score of $score.";
            } else {
                echo "Unfortunately, you didn't pass this time. Your score was $score. You needed at least 15 to pass.";
            }
            ?>
        </div>
        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
