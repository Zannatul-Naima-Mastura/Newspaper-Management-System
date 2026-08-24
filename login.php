<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Newspaper Management System - Login</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="login-container">

        <div class="login-box">

            <h1>Newspaper Management System</h1>

            <h2>Login</h2>

            <form action="authenticate.php" method="POST">

                <div class="form-group">
                    <label for="email">Email / Username</label>

                    <input
                        type="text"
                        id="email"
                        name="login"
                        placeholder="Enter your email or username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit">Login</button>

            </form>

            <p class="info">
                Newspaper Management System
            </p>

        </div>

    </div>

</body>
</html>