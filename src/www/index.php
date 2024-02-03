<?php
// Funkcja, która odpowiada za połączenie z bazą danych Posgtres
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

// Funkcja pobierająca kategorie z bazy danych, jako argument przekazywane jest połączenie z bazą danych, przy pomocy, którego wykonywane jest query. 
// Zwracane jest array ze wszystkimi kategoriami 
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


// Funkcja pobierająca użytkowników dla danej kategorii,
// Wproawdzane są dwa argumenty, połączenie z bazą danych oraz id kategorii, której szukamy
// Zwracane jest array 
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

// Funkcja wysyłająca e-mail, korzysta z funkcji mail()
// Dla uproszczenia działania i testowania wynik jest wyświetlany na ekranie zamiast bycia wysyłanym
// Funkcja przyjmuje 3 argumenty: adresat, temat oraz wiadomość
function sendEmail($recipient, $subject, $message) {
    
    echo "Wysłano wiadomość do: $recipient<br>";
    echo "Temat: $subject<br>";
    echo "Treść wiadomości: $message<br>";
    echo "<hr>";

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
// Element kodu odpowiadający za logikę skryptu
// Sprawdzamy, czy formularz został przesłany, i jeśli tak to odpowiednio wykonujemy wszystkie polecenia. 
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $selectedCategoryId = $_POST["category"];
    $emailSubject = $_POST["subject"];

    $emailMessage = isset($_POST["message"]) && !empty($_POST["message"]) ? $_POST["message"] : "Domyślna treść wiadomości do użytkownika";
    $conn = connectToDatabase();
    $users = getUsersByCategory($conn, $selectedCategoryId);

    foreach ($users as $user) {
        $to = $user["email"];
        $fullName = $user["first_name"] . " " . $user["last_name"];
        $personalizedMessage = "Cześć $fullName,\n\n$emailMessage ";

        $personalizedMessage = str_replace('{imie}', $user['first_name'], $personalizedMessage);
        $personalizedMessage = str_replace('{nazwisko}', $user['last_name'], $personalizedMessage);

        sendEmail($to, $emailSubject, $personalizedMessage);
    }
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