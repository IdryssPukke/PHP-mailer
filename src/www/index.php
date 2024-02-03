<?php
// Połączenie z bazą danych
function connectToDatabase() {
    $host = "postgres";
    $port = 5432;
    $dbname = "mailer";
    $user = "root";
    $password = "root";

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

    try {
        $conn = new PDO($dsn);   
        return $conn;
    } catch (PDOException $e) {
        die("Błąd połączenia z bazą danych: " . $e->getMessage());
    }
}

// Funkcja pobierająca kategorie z bazy danych
function getCategories($conn) {
    $query = "SELECT * FROM categories";
    $result = $conn->query($query);

    if (!$result) {
        die("Błąd zapytania: " . $conn->errorInfo()[2]);
    }

    $categories = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }

    return $categories;
}


// Funkcja pobierająca użytkowników dla danej kategorii
function getUsersByCategory($conn, $categoryId) {
    $query = "SELECT users.* FROM users
              JOIN user_category ON users.id = user_category.user_id
              WHERE user_category.category_id = :categoryId";

    $statement = $conn->prepare($query);
    $statement->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!$result) {
        die("Błąd zapytania: " . $conn->errorInfo()[2]);
    }

    return $result;
}

// Funkcja wysyłająca e-mail
function sendEmail($recipient, $subject, $message) {
    
    // Dla uproszczenia wyświetlam tutaj tylko dane, poniżej przedstawiona jest pełna implementacja funkcji
    echo "Wysłano wiadomość do: $recipient<br>";
    echo "Temat: $subject<br>";
    echo "Treść wiadomości: $message<br>";
    echo "<hr>";

    // Wysłanie e-maila
    // if (mail($recipient, $subject, $message)) {
    //     echo "Wiadomość została wysłana do: $recipient<br>";
    //     echo "Temat: $subject<br>";
    //     echo "Treść wiadomości: $message<br>";
    //     echo "<hr>";
    // } else {
    //     echo "Błąd podczas wysyłania wiadomości do: $recipient";
    // }
}

// Sprawdzamy, czy formularz został przesłany
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedCategoryId = $_POST["category"];
    $emailSubject = $_POST["subject"];

    // Sprawdź, czy treść wiadomości jest dostarczona przez formularz, a jeśli nie to dostarczamy domyślną treść wiadomości
    $emailMessage = isset($_POST["message"]) && !empty($_POST["message"]) ? $_POST["message"] : "Domyślna treść wiadomości do użytkownika";
    echo "emailmessage: ", $emailMessage;
    // Nawiązywanie połączenia z bazą danych PostgreSQL
    $conn = connectToDatabase();

    // Pobieramy użytkowników dla danej kategorii
    $users = getUsersByCategory($conn, $selectedCategoryId);

    // Wysyłamy wiadomość do każdego użytkownika
    foreach ($users as $user) {
        $recipient = $user['email'];
        sendEmail($recipient, $emailSubject, $emailMessage);
    }

    // Zamykamy połączenie z bazą danych
    $conn = null;

    echo "E-maile zostały wysłane do użytkowników w wybranej kategorii.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Php mailer</title>
</head>
<body>
    <h2>Formularz wysyłania emaili</h2>
    <form method="post">
        <label for="category">Wybierz kategorię:</label>
        <select name="category" id="category">
            <?php
            $conn = connectToDatabase();
            $categories = getCategories($conn);

            foreach ($categories as $category) {
                echo "<option value='" . htmlspecialchars($category['id']) . "'>" . htmlspecialchars($category['category_name']) . "</option>";
            }

            $conn = null;
            ?>
        </select>
        <br><br>
        <label for="subject">Temat wiadomości:</label>
        <input type="text" name="subject" id="subject" required>
        <br><br>
        <label for="message">Treść wiadomości:</label>
        <textarea name="message" id="message" rows="20" cols="100"></textarea>
        <br><br>
        <input type="submit" value="Wyślij E-maile">
    </form>
</body>
</html>