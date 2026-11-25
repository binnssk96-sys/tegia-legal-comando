<?php
declare(strict_types=1);

namespace Tegia\Formulario;

use PDO;

/** Acceso a la base de datos. Solo consultas preparadas. SQL compatible con MySQL y SQLite (pruebas). */
final class Repositorio
{
    public function __construct(private PDO $db)
    {
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function registrarIntento(string $ipHmac, string $ahora): void
    {
        $this->db->prepare('INSERT INTO intentos (ip_hmac, creado_en) VALUES (?, ?)')->execute([$ipHmac, $ahora]);
    }

    public function contarIntentos(string $ipHmac, string $desde): int
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM intentos WHERE ip_hmac = ? AND creado_en >= ?');
        $st->execute([$ipHmac, $desde]);
        return (int) $st->fetchColumn();
    }

    /**
     * Guarda contacto, consulta y autorizaciones en una sola transacción.
     * @param array{correo:string,nombre:string,telefono:string,area:string,relato_cifrado:?string,
     *   prefiere_whatsapp:bool,origen:string,ahora:string,ip:string,navegador:string,
     *   autorizaciones:list<array{tipo:string,version:string,texto:string,token_sha256:?string,token_expira_en:?string}>} $d
     * @return int id de la consulta
     */
    public function guardarSolicitud(array $d): int
    {
        $this->db->beginTransaction();
        try {
            $contactoId = $this->guardarContacto($d['correo'], $d['nombre'], $d['telefono'], $d['ahora']);

            $this->db->prepare(
                'INSERT INTO consultas (contacto_id, area, relato_cifrado, prefiere_whatsapp, estado, origen, creada_en)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([$contactoId, $d['area'], $d['relato_cifrado'], $d['prefiere_whatsapp'] ? 1 : 0, 'nueva', $d['origen'], $d['ahora']]);
            $consultaId = (int) $this->db->lastInsertId();

            $st = $this->db->prepare(
                'INSERT INTO autorizaciones (contacto_id, consulta_id, tipo, version_texto, texto, texto_sha256,
                   otorgada_en, ip, navegador, canal, token_sha256, token_expira_en)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($d['autorizaciones'] as $a) {
                $st->execute([
                    $contactoId, $consultaId, $a['tipo'], $a['version'], $a['texto'], hash('sha256', $a['texto']),
                    $d['ahora'], $d['ip'], $d['navegador'], $d['origen'], $a['token_sha256'], $a['token_expira_en'],
                ]);
            }
            $this->db->commit();
            return $consultaId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function guardarContacto(string $correo, string $nombre, string $telefono, string $ahora): int
    {
        $st = $this->db->prepare('SELECT id FROM contactos WHERE correo = ?');
        $st->execute([$correo]);
        $id = $st->fetchColumn();
        if ($id !== false) {
            $this->db->prepare('UPDATE contactos SET nombre = ?, telefono = ?, actualizado_en = ? WHERE id = ?')
                ->execute([$nombre, $telefono, $ahora, $id]);
            return (int) $id;
        }
        $this->db->prepare('INSERT INTO contactos (correo, nombre, telefono, creado_en, actualizado_en) VALUES (?, ?, ?, ?, ?)')
            ->execute([$correo, $nombre, $telefono, $ahora, $ahora]);
        return (int) $this->db->lastInsertId();
    }

    public function marcarCorreos(int $consultaId, bool $acuse, bool $aviso): void
    {
        $this->db->prepare('UPDATE consultas SET acuse_enviado = ?, aviso_enviado = ? WHERE id = ?')
            ->execute([$acuse ? 1 : 0, $aviso ? 1 : 0, $consultaId]);
    }

    /**
     * Doble confirmación de la autorización de correos.
     * @return 'confirmada'|'ya_confirmada'|'vencida'|'invalida'
     */
    public function confirmarCorreos(string $tokenSha256, string $ahora, string $ip): string
    {
        $st = $this->db->prepare(
            "SELECT id, token_expira_en, confirmada_en, revocada_en FROM autorizaciones WHERE token_sha256 = ? AND tipo = 'correos'"
        );
        $st->execute([$tokenSha256]);
        $fila = $st->fetch();
        if (!$fila || $fila['revocada_en'] !== null) {
            return 'invalida';
        }
        if ($fila['confirmada_en'] !== null) {
            return 'ya_confirmada';
        }
        if ($fila['token_expira_en'] !== null && $fila['token_expira_en'] < $ahora) {
            return 'vencida';
        }
        $this->db->prepare('UPDATE autorizaciones SET confirmada_en = ?, ip_confirmacion = ? WHERE id = ? AND confirmada_en IS NULL')
            ->execute([$ahora, $ip, $fila['id']]);
        return 'confirmada';
    }
}
