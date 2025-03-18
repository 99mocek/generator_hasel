'''bash
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Haseł</title>
    <script>
        function validatePassword() {
            let password = document.getElementById("userPassword").value;
            let message = "";
            let specialChar = /[!@#$%^&*()]/;
            let upperCase = /[A-Z]/;

            if (password.length < 8) {
                message += "Hasło musi mieć co najmniej 8 znaków.\n";
            }
            if (!specialChar.test(password)) {
                message += "Hasło musi zawierać co najmniej jeden znak specjalny.\n";
            }
            if (!upperCase.test(password)) {
                message += "Hasło musi zawierać co najmniej jedną dużą literę.\n";
            }

            if (message === "") {
                alert("Hasło jest poprawne.");
            } else {
                alert(message);
            }
        }
    </script>
</head>
<body>
    <h2>Generator Haseł</h2>
    <form method="POST">
        <label for="length">Długość hasła (max 16 znaków):</label>
        <input type="number" id="length" name="length" min="8" max="16" value="12">
        <button type="submit" name="generate">Generuj hasło</button>
    </form>
    
    <?php
    function generatePassword($length) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $maxIndex)];
        }

        return $password;
    }

    function passwordExists($pdo, $password) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM passwords WHERE password = :password");
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    function savePasswordToDB($pdo, $password) {
        $stmt = $pdo->prepare("INSERT INTO passwords (password) VALUES (:password)");
        $stmt->bindParam(':password', $password);
        return $stmt->execute();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['generate'])) {
        try {
            $dsn = 'mysql:host=localhost;dbname=passwords;charset=utf8mb4';
            $username = 'root';
            $password_db = '';

            $pdo = new PDO($dsn, $username, $password_db, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $length = isset($_POST['length']) ? (int)$_POST['length'] : 12;
            $length = max(8, min($length, 16));

            do {
                $password = generatePassword($length);
            } while (passwordExists($pdo, $password));

            if (savePasswordToDB($pdo, $password)) {
                echo "<p>Wygenerowane hasło: <strong>$password</strong></p>";
            } else {
                echo "<p>Nie udało się zapisać hasła.</p>";
            }
        } catch (PDOException $e) {
            echo "<p>Błąd bazy danych: " . $e->getMessage() . "</p>";
        }
    }
    ?>
    
    <h2>Sprawdź swoje hasło</h2>
    <label for="userPassword">Wpisz hasło:</label>
    <input type="password" id="userPassword">
    <button type="button" onclick="validatePassword()">Sprawdź</button>
</body>
</html>
