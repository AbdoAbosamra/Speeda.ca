<?php

return [
    'not_found' => 'La ressource demandée n\'a pas été trouvée.',
    'forbidden' => 'Vous n\'avez pas la permission d\'accéder à cette ressource.',
    'unauthorized' => 'Vous devez être connecté pour accéder à cette ressource.',
    'csrf_token_expired' => 'Votre session a expiré. Veuillez actualiser la page et réessayer.',
    'csrf_token_expired_message' => 'Votre session a expiré pour des raisons de sécurité. Veuillez actualiser la page et réessayer.',
    'server_error' => 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.',
    'generic_error' => 'Une erreur s\'est produite lors du traitement de votre demande. Veuillez réessayer.',
    'duplicate_entry' => 'Ces informations sont déjà utilisées. Veuillez utiliser des détails différents.',
    'foreign_key_constraint' => 'Cet enregistrement ne peut pas être modifié car il est référencé par d\'autres données.',
    'database_error' => 'Une erreur de base de données s\'est produite. Veuillez contacter le support si cela persiste.',
    'file_upload_error' => 'Une erreur s\'est produite lors du téléchargement de votre fichier. Veuillez réessayer.',
    'validation_failed' => 'Veuillez vérifier votre saisie et réessayer.',

    // Error pages (resources/views/errors)
    'page_403_title' => 'Accès refusé',
    'page_403_desc' => 'Vous n\'avez pas la permission d\'ouvrir cette page. Si vous pensez qu\'il s\'agit d\'une erreur, connectez-vous avec le bon compte ou contactez-nous.',
    'page_419_title' => 'Votre session a expiré',
    'page_419_desc' => 'Pour votre sécurité, la page est restée ouverte trop longtemps. Revenez en arrière, actualisez la page et réessayez.',
    'page_429_title' => 'Trop de tentatives',
    'page_429_desc' => 'Vous avez essayé trop de fois en peu de temps. Patientez un instant puis réessayez.',
    'page_429_retry' => 'Vous pourrez réessayer dans :seconds secondes.',
    'page_500_title' => 'Une erreur est survenue',
    'page_500_desc' => 'Une erreur inattendue s\'est produite de notre côté et a été enregistrée pour notre équipe. Veuillez réessayer dans quelques minutes.',
    'go_back' => 'Retour',
    'try_again' => 'Réessayer',
    'contact_support' => 'Contacter le support',
];
