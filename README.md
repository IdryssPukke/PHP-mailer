# PHP-mailer

## opis projektu

Projekt PHP-mailer służy do wysyłania emaili do użytkowników zdefiniowanych w bazie danych w połączeniu z przypisanymi do nich kategoriami.
Dokładniejsze specyfikacje na temat projektu:
1. Jeden użytkownik może być przypisany do wielu kategorii.
2. Treść wiadomości może być podana w polu formularza lub być predefiniowana w kodzie skryptu

### Kroki instalacji
1. Sklonuj repozytorium na swój lokalny komputer:
   git clone https://github.com/twoj-uzytkownik/nazwa-projektu.git
   cd nazwa-projektu
   docker-compose up

2. Wypełnij bazę danych przykładowymi danymi
3. Wykonaj własny setup php i metody mail(), pozwoli to na wysyłanie maili


### Struktura bazy danych
1. Tabela users:
    - id (INT): Unikalny identyfikator użytkownika, klucz główny.
    - first_name (VARCHAR(50)): Imię użytkownika.
    - last_name (VARCHAR(50)): Nazwisko użytkownika.
    - email (VARCHAR(100)): Adres e-mail użytkownika.

2. Tabela categories:
    - id (INT): Unikalny identyfikator kategorii, klucz główny.
    - category_name (VARCHAR(50)): Nazwa kategorii.

3. Tabela user_category:
    - user_id (INT): Klucz obcy odnoszący się do kolumny id w tabeli users.
    - category_id (INT): Klucz obcy odnoszący się do kolumny id w tabeli categories.
    - PRIMARY KEY (user_id, category_id): Klucz główny, łączący obie kolumny i reprezentujący relację wielu do wielu między użytkownikami a kategoriami.
    - FOREIGN KEY (user_id) REFERENCES users(id): Klucz obcy, odnoszący się do kolumny id w tabeli users.
    - FOREIGN KEY (category_id) REFERENCES categories(id): Klucz obcy, odnoszący się do kolumny id w tabeli categories.

Tabela user_category jest tabelą pośrednią (junction table), która umożliwia reprezentację relacji wielu do wielu między użytkownikami a kategoriami.