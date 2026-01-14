<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\TipoPuerta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ImportarPuertasDesdeCsvSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/data/puertas.csv');
        if (!is_file($csvPath)) {
            $this->command?->error("No se encontró el CSV en: {$csvPath}");
            return;
        }

        $file = new \SplFileObject($csvPath);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $file->setCsvControl(',', '"', "\\");

        $header = null;

        /** @var array<string, array<string,mixed>> $molineteGroups */
        $molineteGroups = [];

        $creadas = 0;
        $actualizadas = 0;
        $omitidas = 0;

        foreach ($file as $row) {
            if (!is_array($row) || (count($row) === 1 && ($row[0] === null || $row[0] === ''))) {
                continue;
            }

            // Header
            if ($header === null) {
                $header = array_map(fn ($h) => $this->normalizeHeader((string) $h), $row);
                continue;
            }

            $data = [];
            foreach ($header as $i => $key) {
                if ($key === '') continue;
                $data[$key] = $row[$i] ?? null;
            }

            $pasillo = $this->clean((string) ($data['pasillo'] ?? ''));
            $operacion = Str::lower($this->clean((string) ($data['operacion'] ?? $data['operación'] ?? '')));
            $perfil = $this->clean((string) ($data['perfil_de_usuario'] ?? $data['perfil_de_usuario'] ?? ''));
            $ip = $this->clean((string) ($data['direccion_ip'] ?? $data['dirección_ip'] ?? ''));
            $habilitada = $this->toBool($data['habilitada'] ?? null);
            $nombre = $this->clean((string) ($data['nombre'] ?? $data['nombre_'] ?? ''));
            $pisoRaw = $this->clean((string) ($data['piso'] ?? ''));
            $tipoRaw = $this->clean((string) ($data['tipo_puerta'] ?? ''));
            $materialRaw = $this->clean((string) ($data['material'] ?? ''));
            $alto = $this->toDecimal($data['alto'] ?? null);
            $largo = $this->toDecimal($data['largo'] ?? null);
            $ancho = $this->toDecimal($data['ancho'] ?? null);
            $peso = $this->toDecimal($data['peso'] ?? null);
            $descripcion = $this->clean((string) ($data['descripcion'] ?? ''));
            $codigoBase = $this->clean((string) ($data['codigo_fisico'] ?? $data['codigo_fisico'] ?? $data['codigo fisico'] ?? $data['codigo_fisico_'] ?? ''));

            if ($descripcion === '#REF!') {
                $descripcion = '';
            }

            // Filas sin identificador usable: omitir (ej: fila deshabilitada sin nombre/código)
            if ($nombre === '' && $codigoBase === '') {
                $omitidas++;
                continue;
            }

            $tipoCodigo = $tipoRaw !== '' ? Str::slug($tipoRaw, '_') : null;
            $tipoPuertaId = null;
            if ($tipoCodigo) {
                $tipo = TipoPuerta::query()->updateOrCreate(
                    ['codigo' => $tipoCodigo],
                    ['nombre' => $tipoRaw ?: $tipoCodigo, 'activo' => true]
                );
                $tipoPuertaId = $tipo->id;
            }

            $materialId = null;
            if ($materialRaw !== '') {
                $material = Material::query()->updateOrCreate(
                    ['nombre' => $materialRaw],
                    ['nombre' => $materialRaw, 'activo' => true]
                );
                $materialId = $material->id;
            }

            $pisoId = $this->resolvePisoId($pisoRaw);

            $isMolinete = Str::contains(Str::lower($tipoRaw), 'molinete');
            $requiereDiscapacidad = Str::contains(Str::lower($perfil), ['disc', 'discap']);

            // MOLINETE: viene en 2 filas (Entrada/Salida) y se arma una sola Puerta con ip_entrada + ip_salida
            if ($isMolinete) {
                $groupKey = Str::lower(trim($pasillo)) . '|' . Str::lower(trim($perfil)) . '|' . Str::lower(trim($codigoBase ?: $nombre));
                if (!isset($molineteGroups[$groupKey])) {
                    $molineteGroups[$groupKey] = [
                        'pasillo' => $pasillo,
                        'perfil' => $perfil,
                        'nombre' => $nombre ?: ($codigoBase ?: "{$pasillo} - {$perfil}"),
                        'codigo_base' => $codigoBase ?: Str::upper(Str::slug(($pasillo . '-' . $perfil), '-')),
                        'activo' => $habilitada,
                        'piso_id' => $pisoId,
                        'tipo_puerta_id' => $tipoPuertaId,
                        'material_id' => $materialId,
                        'alto' => $alto,
                        'largo' => $largo,
                        'ancho' => $ancho,
                        'peso' => $peso,
                        'descripcion' => $descripcion ?: null,
                        'requiere_discapacidad' => $requiereDiscapacidad,
                        'ip_entrada' => null,
                        'ip_salida' => null,
                    ];
                }

                if ($operacion === 'entrada') {
                    $molineteGroups[$groupKey]['ip_entrada'] = $ip ?: $molineteGroups[$groupKey]['ip_entrada'];
                } elseif ($operacion === 'salida') {
                    $molineteGroups[$groupKey]['ip_salida'] = $ip ?: $molineteGroups[$groupKey]['ip_salida'];
                }

                // No creamos aún; se crean al final para tener ambas IPs
                continue;
            }

            // PUERTA normal: una fila = una puerta (solo entrada)
            $codigoFisico = $codigoBase !== '' ? $codigoBase : Str::upper(Str::slug(($pasillo . '-' . $perfil), '-'));

            $attrs = [
                'zona_id' => null,
                'piso_id' => $pisoId,
                'tipo_puerta_id' => $tipoPuertaId,
                'material_id' => $materialId,
                'ip_entrada' => $ip ?: null,
                'ip_salida' => null,
                'tiempo_apertura' => 5,
                'alto' => $alto,
                'largo' => $largo,
                'ancho' => $ancho,
                'peso' => $peso,
                'nombre' => $nombre ?: $codigoFisico,
                'ubicacion' => trim(($pisoRaw ?: '') . ($pasillo ? " - {$pasillo}" : '') . ($perfil ? " - {$perfil}" : '')) ?: null,
                'descripcion' => $descripcion ?: null,
                'codigo_fisico_salida' => null,
                'requiere_discapacidad' => $requiereDiscapacidad,
                'es_oculta' => false,
                'activo' => $habilitada,
            ];

            $wasNew = !Puerta::query()->where('codigo_fisico', $codigoFisico)->exists();
            Puerta::query()->updateOrCreate(['codigo_fisico' => $codigoFisico], $attrs);
            $wasNew ? $creadas++ : $actualizadas++;
        }

        // Crear puertas tipo MOLINETE agrupadas
        foreach ($molineteGroups as $g) {
            $base = (string) $g['codigo_base'];
            $codigoEntrada = $this->limitCode($base . '-ENT');
            $codigoSalida = $this->limitCode($base . '-SAL');

            $attrs = [
                'zona_id' => null,
                'piso_id' => $g['piso_id'],
                'tipo_puerta_id' => $g['tipo_puerta_id'],
                'material_id' => $g['material_id'],
                'ip_entrada' => $g['ip_entrada'],
                'ip_salida' => $g['ip_salida'],
                'tiempo_apertura' => 5,
                'alto' => $g['alto'],
                'largo' => $g['largo'],
                'ancho' => $g['ancho'],
                'peso' => $g['peso'],
                'nombre' => (string) $g['nombre'],
                'ubicacion' => trim(($g['pasillo'] ? $g['pasillo'] : '') . ($g['perfil'] ? " - {$g['perfil']}" : '')) ?: null,
                'descripcion' => $g['descripcion'],
                'codigo_fisico_salida' => $codigoSalida,
                'requiere_discapacidad' => (bool) $g['requiere_discapacidad'],
                'es_oculta' => false,
                'activo' => (bool) $g['activo'],
            ];

            $wasNew = !Puerta::query()->where('codigo_fisico', $codigoEntrada)->exists();
            Puerta::query()->updateOrCreate(['codigo_fisico' => $codigoEntrada], $attrs);
            $wasNew ? $creadas++ : $actualizadas++;
        }

        $this->command?->info("Puertas importadas desde CSV. Creadas: {$creadas}, actualizadas: {$actualizadas}, omitidas: {$omitidas}.");
    }

    private function normalizeHeader(string $h): string
    {
        $h = trim($h);
        $h = Str::lower($h);
        $h = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $h);
        $h = preg_replace('/\s+/', '_', $h) ?: '';
        $h = preg_replace('/[^a-z0-9_]/', '', $h) ?: '';
        return $h;
    }

    private function clean(string $v): string
    {
        $v = trim($v);
        $v = preg_replace('/\s+/', ' ', $v) ?: '';
        return trim($v);
    }

    private function toBool($v): bool
    {
        $s = Str::lower($this->clean((string) ($v ?? '')));
        return in_array($s, ['1', 'true', 'si', 'sí', 'yes', 'y'], true);
    }

    private function toDecimal($v): ?float
    {
        $s = $this->clean((string) ($v ?? ''));
        if ($s === '' || $s === '#REF!') return null;
        // CSV viene con coma decimal (ej: "2,44")
        $s = str_replace('.', '', $s); // por si viene separador de miles
        $s = str_replace(',', '.', $s);
        if (!is_numeric($s)) return null;
        return (float) $s;
    }

    private function resolvePisoId(string $raw): ?int
    {
        $raw = $this->clean($raw);
        if ($raw === '') return null;

        $n = Str::lower($raw);
        $nombre = $raw;

        if (Str::contains($n, ['sub', '-1'])) {
            $nombre = 'Subterráneo';
        } elseif (Str::contains($n, ['mezz', 'meza', 'mezan'])) {
            $nombre = 'Mezzanina';
        } elseif (preg_match('/piso\s*(\d+)/i', $raw, $m)) {
            $nombre = 'Piso ' . (int) $m[1];
        }

        $piso = Piso::query()->where('nombre', $nombre)->first();
        if ($piso) return $piso->id;

        // Si no existe, lo creamos para no romper importación
        $orden = (int) (Piso::query()->max('orden') ?? 0) + 1;
        $piso = Piso::query()->create([
            'nombre' => $nombre,
            'orden' => $orden,
            'activo' => true,
        ]);
        return $piso->id;
    }

    private function limitCode(string $code): string
    {
        $code = $this->clean($code);
        if (strlen($code) <= 50) return $code;
        return substr($code, 0, 50);
    }
}

