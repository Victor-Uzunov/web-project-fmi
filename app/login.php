<?php


require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$message = '';


if (isLoggedIn()) {
    header("Location: all_courses.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "<p class='text-red-600 font-semibold mb-4'>Please fill in all fields for login.</p>";
    } else {
        if (loginUser($username, $password)) {
            header("Location: all_courses.php");
            exit();
        } else {
            $message = "<p class='text-red-600 font-semibold mb-4'>Invalid username or password.</p>";
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];

    if (empty($username) || empty($password)) {
        $message = "<p class='text-red-600 font-semibold mb-4'>Please fill in all fields for registration.</p>";
    } else {

        if (strlen($password) < 6) {
             $message = "<p class='text-red-600 font-semibold mb-4'>Password must be at least 6 characters long.</p>";
        } else {
            $registration_response = registerUser($username, $password);

            if ($registration_response['success']) {
                $message = "<p class='text-green-600 font-semibold mb-4'>{$registration_response['message']}</p>";





            } else {
                $message = "<p class='text-red-600 font-semibold mb-4'>{$registration_response['message']}</p>";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - University Course Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
    </style>
</head>
<body class="p-8">
    <div class="container mx-auto max-w-md bg-white rounded-lg shadow-xl p-8">
        <h1 class="text-3xl font-bold text-center text-indigo-700 mb-6">Welcome to Course Manager</h1>

        <?php echo $message;

        <div class="mb-8 p-6 bg-blue-50 rounded-lg shadow-sm">
            <h2 class="text-2xl font-semibold text-blue-700 mb-4">Login</h2>
            <form action="login.php" method="post" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
                    <input type="text" id="username" name="username" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <button type="submit" name="login"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Login
                </button>
            </form>
        </div>

        <div class="p-6 bg-green-50 rounded-lg shadow-sm">
            <h2 class="text-2xl font-semibold text-green-700 mb-4">Register</h2>
            <form action="login.php" method="post" class="space-y-4">
                <div>
                    <label for="reg_username" class="block text-sm font-medium text-gray-700">Username:</label>
                    <input type="text" id="reg_username" name="reg_username" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="reg_password" class="block text-sm font-medium text-gray-700">Password:</label>
                    <input type="password" id="reg_password" name="reg_password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <button type="submit" name="register"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Register New Account
                </button>
            </form>
        </div>
    </div>
</body>
</html>
