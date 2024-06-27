<?php
define('SECRET_KEY', 'a33PSCKskumLb7Os9kwKyrC7qmdk+ETgD7sNSiN7oK8='); // Pakeiskite į sugeneruotą raktą

function register_user($username, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $user_file = 'data/' . $username . '.csv';
    if (!file_exists($user_file)) {
        $file = fopen($user_file, 'w');
        fputcsv($file, [$hashed_password]);
        fclose($file);
        echo "Vartotojas sėkmingai užregistruotas!";
    } else {
        echo "Vartotojas jau egzistuoja!";
    }
}

function login_user($username, $password) {
    $user_file = 'data/' . $username . '.csv';
    if (file_exists($user_file)) {
        decrypt_file($user_file);

        // Patikriname failo turinį po dešifravimo
        $file_content = file_get_contents($user_file);
        echo "Failo turinys po dešifravimo: " . htmlspecialchars($file_content) . "<br>";

        $file = fopen($user_file, 'r');
        $data = fgetcsv($file);
        fclose($file);
        encrypt_file($user_file);

        if ($data !== false && isset($data[0])) {
            $hashed_password = $data[0];
            if (password_verify($password, $hashed_password)) {
                $_SESSION['username'] = $username;
                echo "Sėkmingai prisijungta!";
            } else {
                echo "Neteisingas slaptažodis!";
            }
        } else {
            echo "Klaida skaitant vartotojo duomenis: " . print_r($data, true);
        }
    } else {
        echo "Vartotojas nerastas!";
    }
}

function encrypt_file($filename) {
    if (file_exists($filename)) {
        $plaintext = file_get_contents($filename);
        if ($plaintext === false || $plaintext === '') {
            echo "Failas tuščias arba nepavyko perskaityti failo turinio prieš šifruojant.<br>";
            return;
        }
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', SECRET_KEY, 0, $iv);
        if ($ciphertext === false) {
            echo "Klaida šifruojant failą.<br>";
            return;
        }
        file_put_contents($filename, base64_encode($iv . $ciphertext));
    }
}

function decrypt_file($filename) {
    if (file_exists($filename)) {
        $data = base64_decode(file_get_contents($filename));
        if ($data === false) {
            echo "Klaida dekoduojant failą.<br>";
            return;
        }
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $iv_length);
        $ciphertext = substr($data, $iv_length);
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', SECRET_KEY, 0, $iv);
        if ($plaintext === false) {
            echo "Klaida dešifruojant failą.<br>";
            return;
        }
        //echo "Dešifruotas tekstas: " . htmlspecialchars($plaintext) . "<br>"; // Diagnostikos pranešimas
        file_put_contents($filename, $plaintext);
    }
}

function add_password($name, $password, $url, $comment) {
    $username = $_SESSION['username'];
    $user_file = 'data/' . $username . '.csv';
    decrypt_file($user_file);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted_password = openssl_encrypt($password, 'aes-256-cbc', SECRET_KEY, 0, $iv);
    if ($encrypted_password === false) {
        echo "Klaida šifruojant slaptažodį.<br>";
        return;
    }
    $file = fopen($user_file, 'a');
    fputcsv($file, [$name, base64_encode($iv . $encrypted_password), $url, $comment]);
    fclose($file);
    encrypt_file($user_file);
}

function search_password($name) {
    $username = $_SESSION['username'];
    $user_file = 'data/' . $username . '.csv';
    decrypt_file($user_file);
    $file = fopen($user_file, 'r');
    $found = false;
    while ($row = fgetcsv($file)) {
        if ($row[0] === $name) {
            echo "Pavadinimas: " . $row[0] . "<br>";
            echo "Šifruotas slaptažodis: " . $row[1] . "<br>";
            echo '<form method="post"><button type="submit" name="show_password" value="' . $row[1] . '">Rodyti slaptažodį</button></form>';
            echo "URL/Aplikacija: " . $row[2] . "<br>";
            echo "Komentaras: " . $row[3] . "<br>";
            $found = true;
            break;
        }
    }
    fclose($file);
    encrypt_file($user_file);
    if (!$found) {
        echo "Slaptažodis nerastas.";
    }
}

if (isset($_POST['show_password'])) {
    $encrypted_password = $_POST['show_password'];
    $data = base64_decode($encrypted_password);
    if ($data === false) {
        echo "Klaida dekoduojant šifruotą slaptažodį.<br>";
        return;
    }
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $iv_length);
    $ciphertext = substr($data, $iv_length);
    $decrypted_password = openssl_decrypt($ciphertext, 'aes-256-cbc', SECRET_KEY, 0, $iv);
    if ($decrypted_password === false) {
        echo "Klaida dešifruojant slaptažodį.<br>";
        return;
    }
    echo "Dešifruotas slaptažodis: " . htmlspecialchars($decrypted_password);
}

function update_password($name, $new_password) {
    $username = $_SESSION['username'];
    $user_file = 'data/' . $username . '.csv';
    decrypt_file($user_file);
    $file = fopen($user_file, 'r');
    $rows = [];
    while ($row = fgetcsv($file)) {
        if ($row[0] === $name) {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted_new_password = openssl_encrypt($new_password, 'aes-256-cbc', SECRET_KEY, 0, $iv);
            if ($encrypted_new_password === false) {
                echo "Klaida šifruojant naują slaptažodį.<br>";
                return;
            }
            $row[1] = base64_encode($iv . $encrypted_new_password);
        }
        $rows[] = $row;
    }
    fclose($file);
    $file = fopen($user_file, 'w');
    foreach ($rows as $row) {
        fputcsv($file, $row);
    }
    fclose($file);
    encrypt_file($user_file);
}

function delete_password($name) {
    $username = $_SESSION['username'];
    $user_file = 'data/' . $username . '.csv';
    decrypt_file($user_file);
    $file = fopen($user_file, 'r');
    $rows = [];
    while ($row = fgetcsv($file)) {
        if ($row[0] !== $name) {
            $rows[] = $row;
        }
    }
    fclose($file);
    $file = fopen($user_file, 'w');
    foreach ($rows as $row) {
        fputcsv($file, $row);
    }
    fclose($file);
    encrypt_file($user_file);
}

function generate_random_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}
?>
