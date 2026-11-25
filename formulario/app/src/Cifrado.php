<?php
declare(strict_types=1);

namespace Tegia\Formulario;

/**
 * Cifra el relato del caso con una clave de 32 bytes.
 * - "v2:" AES-256-GCM (OpenSSL). Formato por defecto: OpenSSL viene en todo PHP.
 * - "v1:" XChaCha20-Poly1305 (sodium). Se usa solo si falta OpenSSL; Hostinger no trae sodium activo.
 * Ambos son cifrado autenticado: un dato alterado no se descifra. Se leen los dos formatos.
 */
final class Cifrado
{
    private const DATOS_ASOCIADOS = 'tegia-relato';
    private const GCM_IV = 12;
    private const GCM_TAG = 16;

    private string $clave;
    private bool $openssl;

    public function __construct(string $claveBase64, ?bool $usarOpenssl = null)
    {
        $clave = base64_decode($claveBase64, true);
        if ($clave === false || strlen($clave) !== 32) {
            throw new \InvalidArgumentException('La clave de cifrado debe ser de 32 bytes en base64');
        }
        $this->clave = $clave;
        $this->openssl = $usarOpenssl ?? (extension_loaded('openssl') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true));
        if (!$this->openssl && !extension_loaded('sodium')) {
            throw new \RuntimeException('No hay OpenSSL ni sodium para cifrar');
        }
    }

    public static function nuevaClave(): string
    {
        return base64_encode(random_bytes(32));
    }

    public function cifrar(string $texto): string
    {
        if ($this->openssl) {
            $iv = random_bytes(self::GCM_IV);
            $tag = '';
            $c = openssl_encrypt($texto, 'aes-256-gcm', $this->clave, OPENSSL_RAW_DATA, $iv, $tag, self::DATOS_ASOCIADOS . '-v2', self::GCM_TAG);
            if ($c === false) {
                throw new \RuntimeException('No se pudo cifrar');
            }
            return 'v2:' . base64_encode($iv . $tag . $c);
        }
        $n = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        return 'v1:' . base64_encode($n . sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($texto, self::DATOS_ASOCIADOS . '-v1', $n, $this->clave));
    }

    public function descifrar(string $guardado): string
    {
        $formato = substr($guardado, 0, 3);
        $bin = base64_decode(substr($guardado, 3), true);
        if ($bin === false) {
            throw new \RuntimeException('Dato cifrado dañado');
        }
        if ($formato === 'v2:') {
            if (strlen($bin) < self::GCM_IV + self::GCM_TAG) {
                throw new \RuntimeException('Dato cifrado dañado');
            }
            $texto = openssl_decrypt(substr($bin, self::GCM_IV + self::GCM_TAG), 'aes-256-gcm', $this->clave, OPENSSL_RAW_DATA,
                substr($bin, 0, self::GCM_IV), substr($bin, self::GCM_IV, self::GCM_TAG), self::DATOS_ASOCIADOS . '-v2');
        } elseif ($formato === 'v1:') {
            if (!extension_loaded('sodium')) {
                throw new \RuntimeException('Este dato necesita la extensión sodium');
            }
            $n = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
            if (strlen($bin) <= $n) {
                throw new \RuntimeException('Dato cifrado dañado');
            }
            $texto = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(substr($bin, $n), self::DATOS_ASOCIADOS . '-v1', substr($bin, 0, $n), $this->clave);
        } else {
            throw new \RuntimeException('Formato de cifrado desconocido');
        }
        if ($texto === false) {
            throw new \RuntimeException('No se pudo descifrar (clave incorrecta o dato alterado)');
        }
        return $texto;
    }
}
