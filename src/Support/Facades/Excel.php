<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Export\Excel as ExcelExporter;
use IronFlow\Http\Response;

/**
 * Façade pour l'exportation Excel
 * 
 * @method static Response download(array $data, string $filename, string $delimiter = ',')
 * @method static Response csv(array $data, string $filename)
 */
class Excel
{
   /**
    * Exporte les données au format Excel
    *
    * @param array $data
    * @param string $filename
    * @param string $delimiter
    * @return Response
    */
   public static function download(array $data, string $filename, string $delimiter = ','): Response
   {
      return ExcelExporter::download($data, $filename, $delimiter);
   }

   /**
    * Exporte les données au format CSV
    *
    * @param array $data
    * @param string $filename
    * @return Response
    */
   public static function csv(array $data, string $filename): Response
   {
      return ExcelExporter::csv($data, $filename);
   }
}
