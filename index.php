<?php
session_start();
require 'functions.php';

if (!file_exists('data')) {
    mkdir('data');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        register_user($_POST['username'], $_POST['password']);
    } elseif (isset($_POST['login'])) {
        login_user($_POST['username'], $_POST['password']);
    } elseif (isset($_POST['add_password'])) {
        add_password($_POST['name'], $_POST['password'], $_POST['url'], $_POST['comment']);
    } elseif (isset($_POST['search_password'])) {
        search_password($_POST['name']);
    } elseif (isset($_POST['update_password'])) {
        update_password($_POST['name'], $_POST['new_password']);
    } elseif (isset($_POST['delete_password'])) {
        delete_password($_POST['name']);
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header('Location: index.php');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Slaptažodžių Valdymo Sistema</title>
</head>
<body>
<?php if (!isset($_SESSION['username'])): ?>
    <h2>Registracija</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Slapyvardis" required>
        <input type="password" name="password" placeholder="Slaptažodis" required>
        <button type="submit" name="register">Registruotis</button>
    </form>

    <h2>Prisijungimas</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Slapyvardis" required>
        <input type="password" name="password" placeholder="Slaptažodis" required>
        <button type="submit" name="login">Prisijungti</button>
    </form>
<?php else: ?>
    <h2>Sveiki, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

    <h3>Pridėti Naują Slaptažodį</h3>
    <form method="post">
        <input type="text" name="name" placeholder="Pavadinimas" required>
        <input type="text" name="password" placeholder="Slaptažodis" required>
        <input type="text" name="url" placeholder="URL/Aplikacija" required>
        <input type="text" name="comment" placeholder="Komentaras" required>
        <button type="submit" name="add_password">Pridėti</button>
    </form>

    <h3>Ieškoti Slaptažodžio</h3>
    <form method="post">
        <input type="text" name="name" placeholder="Pavadinimas" required>
        <button type="submit" name="search_password">Ieškoti</button>
    </form>

    <h3>Atnaujinti Slaptažodį</h3>
    <form method="post">
        <input type="text" name="name" placeholder="Pavadinimas" required>
        <input type="text" name="new_password" placeholder="Naujas Slaptažodis" required>
        <button type="submit" name="update_password">Atnaujinti</button>
    </form>

    <h3>Ištrinti Slaptažodį</h3>
    <form method="post">
        <input type="text" name="name" placeholder="Pavadinimas" required>
        <button type="submit" name="delete_password">Ištrinti</button>
    </form>

    <form method="post">
        <button type="submit" name="logout">Atsijungti</button>
    </form>
<?php endif; ?>
</body>
</html>
