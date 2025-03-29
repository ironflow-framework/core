<?php

namespace IronFlow\Framework\AI;

interface AIProvider
{
   /**
    * Génère une réponse textuelle simple à partir d'un prompt
    *
    * @param string $prompt Le prompt pour l'IA
    * @param array $options Options supplémentaires pour la génération
    * @return string La réponse générée
    */
   public function generate(string $prompt, array $options = []): string;

   /**
    * Génère une complétion pour un prompt avec des métadonnées
    *
    * @param string $prompt Le prompt pour l'IA
    * @param array $options Options supplémentaires pour la génération
    * @return array La réponse avec des métadonnées
    */
   public function completion(string $prompt, array $options = []): array;

   /**
    * Génère une réponse de chat à partir d'une série de messages
    *
    * @param array $messages Messages au format [{role, content}]
    * @param array $options Options supplémentaires pour la génération
    * @return array La réponse avec des métadonnées
    */
   public function chat(array $messages, array $options = []): array;
}
