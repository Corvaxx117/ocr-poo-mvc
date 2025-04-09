<?php

// namespace App\Traits;

// trait FlashMessageHandlerTrait
// {
//     /**
//      * Gère automatiquement les messages flash à partir des paramètres GET.
//      */
//     public function handleFlashMessagesFromQuery(): void
//     {
//         $types = ['success', 'error', 'warning'];

//         foreach ($types as $type) {
//             if (isset($_GET[$type]) && $_GET[$type] == 1 && !$this->hasFlash($type)) {
//                 $defaultMessages = [
//                     'success' => "Action réalisée avec succès.",
//                     'error' => "Une erreur est survenue.",
//                     'warning' => "Attention, vérifiez les informations saisies."
//                 ];
//                 $this->addFlash($type, $defaultMessages[$type]);
//             }
//         }
//     }
// }
