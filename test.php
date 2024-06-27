<?php
$secret_key = base64_encode(openssl_random_pseudo_bytes(32)); // 256-bit key for AES-256
echo "Your generated SECRET_KEY is: $secret_key";

//a33PSCKskumLb7Os9kwKyrC7qmdk+ETgD7sNSiN7oK8=
?>
