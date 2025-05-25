<?php
use Illuminate\Support\Facades\File;

if (! function_exists('getKidFromPublicKey')) {
    function getKidFromPublicKey(): string
    {
        $str = env('JWT_PUBLIC_KEY');
        $parsing = explode("storage/", $str);
        
        $publicKeyPath = storage_path($parsing[1]);
        $publicKeyPem = File::get($publicKeyPath);

        $publicKey = openssl_pkey_get_public($publicKeyPem);
        $keyDetails = openssl_pkey_get_details($publicKey);
        $modulus = $keyDetails['rsa']['n'];
        $exponent = $keyDetails['rsa']['e'];

        // SHA-256 fingerprint, base64url encoded without padding
        return rtrim(strtr(base64_encode(hash('sha512', $modulus . $exponent, true)), '+/', '-_'), '=');
    }
}
