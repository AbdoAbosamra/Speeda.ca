<?php

return [
    // Service Provider Profile Update Validation Messages - French

    // Business Name
    'sp_business_name_required' => "Le nom de l'entreprise est requis",
    'sp_business_name_min' => "Le nom de l'entreprise doit contenir au moins 3 caractères",
    'sp_business_name_max' => "Le nom de l'entreprise ne peut pas dépasser 255 caractères",
    'sp_business_name_invalid_chars' => "Le nom de l'entreprise contient des caractères invalides",

    // Bio / Description
    'sp_bio_min' => 'La description doit contenir au moins 10 caractères',
    'sp_bio_max' => 'La description ne peut pas dépasser 2000 caractères',
    'sp_bio_no_html' => 'La description ne peut pas contenir de code HTML',

    // Experience
    'sp_experience_integer' => "Les années d'expérience doivent être un nombre entier",
    'sp_experience_min' => "Les années d'expérience ne peuvent pas être négatives",
    'sp_experience_max' => "Les années d'expérience ne peuvent pas dépasser 50 ans",

    // Hourly Rate
    'sp_hourly_rate_numeric' => 'Le tarif horaire doit être un nombre',
    'sp_hourly_rate_min' => 'Le tarif horaire ne peut pas être négatif',
    'sp_hourly_rate_max' => 'Le tarif horaire ne peut pas dépasser 10000',
    'sp_hourly_rate_format' => 'Le tarif doit être dans un format correct (maximum 2 décimales)',

    // Phone
    'sp_phone_required' => 'Le numéro de téléphone est requis',
    'sp_phone_min' => 'Le numéro de téléphone doit contenir au moins 10 chiffres',
    'sp_phone_max' => 'Le numéro de téléphone ne peut pas dépasser 20 chiffres',
    'sp_phone_format' => 'Le format du numéro de téléphone est incorrect (exemple: +15141234567 ou 514-123-4567)',
    'sp_phone_unique' => 'Ce numéro de téléphone est déjà enregistré',

    // WhatsApp (Obligatoire)
    'sp_whatsapp_country_code_required' => 'Le code pays est requis',
    'sp_whatsapp_country_code_in' => 'Veuillez sélectionner un code pays valide',
    'sp_whatsapp_required' => 'Le numéro WhatsApp est requis',
    'sp_whatsapp_min' => 'Le numéro WhatsApp doit comporter au moins 10 chiffres',
    'sp_whatsapp_max' => 'Le numéro WhatsApp ne peut pas dépasser 15 chiffres',
    'sp_whatsapp_format' => 'Entrez un numéro WhatsApp valide (chiffres; espaces/tirets autorisés)',

    // Email
    'sp_email_required' => "L'adresse e-mail est requise",
    'sp_email_format' => "Le format de l'adresse e-mail est incorrect",
    'sp_email_max' => "L'adresse e-mail ne peut pas dépasser 255 caractères",
    'sp_email_invalid' => "L'adresse e-mail est invalide",

    // Address
    'sp_address_min' => "L'adresse doit contenir au moins 5 caractères",
    'sp_address_max' => "L'adresse ne peut pas dépasser 500 caractères",
    'sp_address_english_only' => "L'adresse doit contenir uniquement des caractères anglais, chiffres et ponctuation",

    // Location
    'sp_location_invalid' => "L'emplacement sélectionné est invalide",

    // Services
    'sp_services_max' => 'Les services offerts ne peuvent pas dépasser 1000 caractères',
    'sp_services_invalid_chars' => 'Les services contiennent des caractères invalides',

    // Profile Image
    'sp_image_type' => "L'image de profil doit être un fichier image",
    'sp_image_mimes' => "L'image de profil doit être au format JPG, JPEG, PNG ou WebP",
    'sp_image_size' => "L'image de profil ne peut pas dépasser 5Mo",
    'sp_image_dimensions' => "Les dimensions de l'image doivent être entre 200x200 et 5000x5000 pixels",

    // Certification
    'sp_cert_file' => 'La certification doit être un fichier',
    'sp_cert_mimes' => 'La certification doit être au format JPG, PNG, WebP ou PDF',
    'sp_cert_size' => 'Le fichier de certification ne peut pas dépasser 10Mo',

    // Category
    'sp_category_invalid' => 'La catégorie/profession sélectionnée est invalide',
];
