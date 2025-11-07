<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DniConsultationService
{
    /**
     * Consulta los datos de un DNI en la API de SUNAT
     *
     * @param string $dni
     * @return array|null
     */
    public function consultDni(string $dni): ?array
    {
        try {
            // Validar formato de DNI (8 dígitos)
            if (!preg_match('/^\d{8}$/', $dni)) {
                return null;
            }

            // URL de la API de SUNAT
            $url = "https://ww1.sunat.gob.pe/ol-ti-itfisdenreg/itfisdenreg.htm";

            // Realizar la petición con timeout de 10 segundos
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json',
                ])
                ->get($url, [
                    'accion' => 'obtenerDatosDni',
                    'numDocumento' => $dni
                ]);

            // Verificar si la petición fue exitosa
            if (!$response->successful()) {
                Log::warning('DNI Consultation API error', [
                    'dni' => $dni,
                    'status' => $response->status()
                ]);
                return null;
            }

            $data = $response->json();

            // Verificar que tenga la estructura esperada
            if (
                !isset($data['message']) ||
                $data['message'] !== 'success' ||
                !isset($data['lista'][0]['nombresapellidos'])
            ) {
                return null;
            }

            // Extraer y formatear el nombre
            $fullName = $this->formatName($data['lista'][0]['nombresapellidos']);

            return [
                'success' => true,
                'full_name' => $fullName,
                'raw_name' => $data['lista'][0]['nombresapellidos']
            ];

        } catch (\Exception $e) {
            Log::error('Error consultando DNI', [
                'dni' => $dni,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Formatea el nombre de "APELLIDOS,NOMBRES" a "NOMBRES APELLIDOS"
     *
     * @param string $rawName
     * @return string
     */
    private function formatName(string $rawName): string
    {
        // Ejemplo: "BOLUARTE ZEGARRA,DINA ERCILIA" -> "DINA ERCILIA BOLUARTE ZEGARRA"

        // Separar por coma
        $parts = explode(',', $rawName);

        if (count($parts) !== 2) {
            // Si no tiene el formato esperado, devolver tal cual
            return $rawName;
        }

        $apellidos = trim($parts[0]);
        $nombres = trim($parts[1]);

        // Formatear: Nombres Apellidos (con capitalización)
        $fullName = $nombres . ' ' . $apellidos;

        // Convertir a formato título (Primera letra mayúscula de cada palabra)
        return ucwords(strtolower($fullName));
    }

    /**
     * Valida si un DNI tiene el formato correcto
     *
     * @param string $dni
     * @return bool
     */
    public function isValidDniFormat(string $dni): bool
    {
        return preg_match('/^\d{8}$/', $dni) === 1;
    }
}
