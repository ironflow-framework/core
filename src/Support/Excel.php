<?php

namespace IronFlow\Support;

use IronFlow\Http\Response;

class Excel
{
    /**
     * Exporte les données au format Excel
     * @param array $data Données à exporter
     * @param string $filename Nom du fichier
     * @return Response
     */
    public static function download(array $data, string $filename): Response
    {
        $headers = array_keys($data[0] ?? []);
        
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $headers);
        
        foreach ($data as $row) {
            fputcsv($fp, array_values($row));
        }
        
        rewind($fp);
        
        $content = stream_get_contents($fp);
        fclose($fp);
        
        return new Response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=" . $filename,
        ]);
    }
}
