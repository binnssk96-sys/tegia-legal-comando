<?php
declare(strict_types=1);

namespace Tegia\Formulario;

/** Cifra el relato del caso con XChaCha20-Poly1305 (libsodium). */
final class Cifrado
{
    private const PREFIJO = 'v1:';
    private const DATOS_ASOCIADOS = 'tegia-relato-v1';

    private string $clave;

    public function __construct(string $claveBase64)
    {
        $clave = base64_decode($claveBase64, true);
        if ($clave === false || strlen($clave) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
            throw new \InvalidArgumentException('La clave de cifrado debe ser de 32 bytes en base64');
        }
        $this->clave = $clave;
    }

    public static function nuevaClave(): string
    {
        return base64_encode(sodium_crypto_aead_xchacha20poly1305_ietf_keygen());
    }

    public function cifrar(string $texto): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $cifrado = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($texto, self::DATOS_ASOCIADOS, $nonce, $this->clave);
        return self::PREFIJO . base64_encode($nonce . $cifrado);
    }

    public function descifrar(string $guardado): string
    {
        if (!str_starts_with($guardado, self::PREFIJO)) {
            throw new \RuntimeException('Formato de cifrado desconocido');
        }
        $bin = base64_decode(substr($guardado, strlen(self::PREFIJO)), true);
        $n = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
        if ($bin === false || strlen($bin) <= $n) {
            throw new \RuntimeException('Dato cifrado dañado');
        }
        $texto = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(substr($bin, $n), self::DATOS_ASOCIADOS, substr($bin, 0, $n), $this->clave);
        if ($texto === false) {
            throw new \RuntimeException('No se pudo descifrar (clave incorrecta o dato alterado)');
        }
        return $texto;
    }
}
