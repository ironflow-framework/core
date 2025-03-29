<?php

return [
   'default' => 'local',
   'disks' => [
      'local' => [
         'root' => storage_path('app'),
         'url' => '/storage/app',
      ],
      'public' => [
         'root' => storage_path('app/public'),
         'url' => '/storage/app/public',
      ],
   ],
];
