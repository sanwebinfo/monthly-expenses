<?php

include __DIR__ . '/./api/Database.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('X-Robots-Tag: noindex, nofollow', true);

$error = "";

$isFormSubmitted = isset($_POST['submit_button']); 

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES | ENT_HTML5);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

if (!isset($_SESSION['csrf_token'])) {
    generate_csrf_token();
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getUserHashedPassword($username, $password) {
    $database = new Database();
    $db = $database->connect();
    $query = 'SELECT password, approved FROM users WHERE username = :username';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $hashed_password = $result['password'];
    $approved = $result['approved'];
    if ($hashed_password && password_verify($password, $hashed_password)) {
        return $approved;
    }
    return false;
}

if ($isFormSubmitted) {

    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "CSRF token verification failed. Action aborted.";
    } else {

        $username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($password) || empty($username)) {
            $error = "Username and password are required.";
        } else {
            $approved = getUserHashedPassword($username, $password);
            $_SESSION['username'] = $username;
            if ($approved) {
                header('Location: /home.php');
                exit();
            } elseif ($approved == 0) {
                $error = "Account not approved.";
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="HandheldFriendly" content="True" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#c7ecee">
<link rel="shortcut icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAA7EAAAOxAGVKw4bAAABqklEQVQ4jZ2Tv0scURDHP7P7SGWh14mkuXJZEH8cgqUWcklAsLBbCEEJSprkD7hD/4BUISHEkMBBiivs5LhCwRQBuWgQji2vT7NeYeF7GxwLd7nl4knMwMDMfL8z876P94TMLt+8D0U0EggQSsAjwMvga8ChJAqxqjTG3m53AQTg4tXHDRH9ABj+zf6oytbEu5d78nvzcyiivx7QXBwy46XOi5z1jbM+Be+nqVfP8yzuD3FM6rzIs9YE1hqGvDf15cVunmdx7w5eYJw1pcGptC9CD4gBUuef5Ujq/BhAlTLIeFYuyfmTZgeYv+2nPt1a371P+Hm1WUPYydKf0lnePwVmh3hnlcO1uc7yvgJUDtdG8oy98kduK2KjeHI0fzCQINSXOk/vlXBUOaihAwnGWd8V5r1uhe1VIK52V6JW2D4FqHZX5lphuwEE7ooyaN7gjLMmKSwYL+pMnV+MA/6+g8RYa2Lg2RBQbj4+rll7uymLy3coiuXb5PdQVf7rKYvojAB8Lf3YUJUHfSYR3XqeLO5JXvk0dhKqSqQQoCO+s5AIxCLa2Lxc6ALcAPwS26XFskWbAAAAAElFTkSuQmCC" />
<?php $current_page = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; echo '<link rel="canonical" href="'.$current_page.'" />'; ?>


    <title>Expense Manager</title>
    <meta name="description" content="A Simple website to Manage your Monthly Expenses and Track your Due date and paid status."/>

    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css"
        integrity="sha512-IgmDkwzs96t4SrChW29No3NXBIBv8baW490zk5aXvhCD8vuZM3yUSkbyTBcXohkySecyzIrUwiF/qV0cuPcL3Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
        rel="stylesheet">

    <style>
        html,
        body {
            min-height: 100vh;
            font-family: "Roboto Mono", monospace;
            background-color: #FDA7DF;
            padding-bottom: 20px;
            font-weight: 600;
            line-height: 1.6;
            word-wrap: break-word;
            -moz-osx-font-smoothing: grayscale;
            -webkit-font-smoothing: antialiased !important;
            -moz-font-smoothing: antialiased !important;
            text-rendering: optimizeLegibility !important;
        }

        input,
        select,
        button {
            font-family: "Roboto Mono", monospace;
        }

        .notification.is-hidden {
            display: none;
        }

        #quote-container {
            margin: 10px auto;
            border-radius: 10px;
            padding: 20px;
            background-color: #fff;
            font-family: "Roboto Mono", monospace;
        }

        #quote {
            font-family: "Roboto Mono", monospace;
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }

        #quote-card {
            max-width: 800px;
            margin: 10px auto;
            font-family: "Roboto Mono", monospace;
        }

        .table-container {
            font-family: "Roboto Mono", monospace;
            overflow-x: auto;
        }
        .user-button {
            font-family: "Roboto Mono", monospace;
            display: flex;
            flex-grow: 0.3;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            border-radius: 32px;
            padding: 12px;
           -moz-osx-font-smoothing: grayscale;
           -webkit-font-smoothing: antialiased !important;
           -moz-font-smoothing: antialiased !important;
           text-rendering: optimizeLegibility !important;
        }
    </style>

</head>
<body>

<section class="section">
<div class="container">
<div id="quote-card" class="card is-rounded">
<div class="card-content">
<div id="quote-container">
<hr>
<h1 class="title is-size-5">Expense Manager</h1>
<br>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="field">
<label for="username" class="label">Username:</label>
<div class="control">
<input type="text" class="input" id="username" name="username" autocomplete="username">
</div>
</div>
<div class="field">
<label for="password" class="label">Password:</label>
<div class="control">
<input type="password" class="input" id="password" name="password" autocomplete="current-password">
</div>
</div>
<?php if (!empty($error)): ?>
<div class="notification is-danger"><button class="delete" onclick="this.parentNode.remove();"></button><P><?= $error; ?></P></span></div>
<?php endif; ?>
<div class="field">
<div class="control">
<input type="submit" class="button is-warning" name="submit_button" value="Submit">
</div>
</div>
</form>
<?php if (!empty($errors)): ?>
<div class="notification is-danger">
<button class="delete" onclick="this.parentNode.remove();"></button>
<?php foreach ($errors as $error): ?>
<p><?php echo $error; ?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>       
</section>

</body>
</html>