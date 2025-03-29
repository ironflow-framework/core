<?php

namespace IronFlow\Support\Export;

use IronFlow\Http\Response;

/**
 * Classe utilitaire pour l'export de données au format Excel
 */
class Excel
{
   /**
    * Exporte les données au format CSV/Excel
    * 
    * @param array $data Données à exporter
    * @param string $filename Nom du fichier
    * @param string $delimiter Délimiteur pour le CSV
    * @return Response
    */
   public static function download(array $data, string $filename, string $delimiter = ','): Response
   {
      $headers = array_keys($data[0] ?? []);

      $fp = fopen('php://temp', 'r+');
      fputcsv($fp, $headers, $delimiter);

      foreach ($data as $row) {
         fputcsv($fp, array_values($row), $delimiter);
      }

      rewind($fp);

      $content = stream_get_contents($fp);
      fclose($fp);

      // Extension appropriée
      if (!str_ends_with($filename, '.csv') && !str_ends_with($filename, '.xlsx')) {
         $filename .= '.xlsx';
      }

      return new Response($content, 200, [
         'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
         'Content-Disposition' => "attachment; filename={$filename}",
      ]);
   }

   /**
    * Exporte les données au format CSV
    * 
    * @param array $data Données à exporter
    * @param string $filename Nom du fichier
    * @return Response
    */
   public static function csv(array $data, string $filename): Response
   {
      if (!str_ends_with($filename, '.csv')) {
         $filename .= '.csv';
      }

      $content = '';
      $headers = array_keys($data[0] ?? []);

      // Créer le contenu CSV manuellement
      $fp = fopen('php://temp', 'r+');
      fputcsv($fp, $headers, ',');

      foreach ($data as $row) {
         fputcsv($fp, array_values($row), ',');
      }

      rewind($fp);
      $content = stream_get_contents($fp);
      fclose($fp);

      return new Response($content, 200, [
         'Content-Type' => 'text/csv',
         'Content-Disposition' => "attachment; filename={$filename}",
      ]);
   }
}
