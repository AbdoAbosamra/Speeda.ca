<?php

return [
    // Service Provider Profile Update Validation Messages - English

    // Business Name
    'sp_business_name_required' => 'Business name is required',
    'sp_business_name_min' => 'Business name must be at least 3 characters',
    'sp_business_name_max' => 'Business name cannot exceed 255 characters',
    'sp_business_name_invalid_chars' => 'Business name contains invalid characters',

    // Bio / Description
    'sp_bio_min' => 'Description must be at least 10 characters',
    'sp_bio_max' => 'Description cannot exceed 2000 characters',
    'sp_bio_no_html' => 'Description cannot contain HTML code',

    // Experience
    'sp_experience_integer' => 'Years of experience must be a whole number',
    'sp_experience_min' => 'Years of experience cannot be negative',
    'sp_experience_max' => 'Years of experience cannot exceed 50 years',

    // Hourly Rate
    'sp_hourly_rate_numeric' => 'Hourly rate must be a number',
    'sp_hourly_rate_min' => 'Hourly rate cannot be negative',
    'sp_hourly_rate_max' => 'Hourly rate cannot exceed 10000',
    'sp_hourly_rate_format' => 'Rate must be in correct format (maximum 2 decimal places)',

    // Phone
    'sp_phone_required' => 'Phone number is required',
    'sp_phone_min' => 'Phone number must be at least 10 digits',
    'sp_phone_max' => 'Phone number cannot exceed 20 digits',
    'sp_phone_format' => 'Phone number format is incorrect (example: +15141234567 or 514-123-4567)',
    'sp_phone_unique' => 'This phone number is already registered',

    // WhatsApp (Required)
    'sp_whatsapp_country_code_required' => 'Country code is required',
    'sp_whatsapp_country_code_in' => 'Please select a valid country code',
    'sp_whatsapp_required' => 'WhatsApp number is required',
    'sp_whatsapp_min' => 'WhatsApp number must be at least 10 digits',
    'sp_whatsapp_max' => 'WhatsApp number cannot exceed 15 digits',
    'sp_whatsapp_format' => 'Enter a valid WhatsApp number (digits; spaces/dashes allowed) ',

    // Email
    'sp_email_required' => 'Email address is required',
    'sp_email_format' => 'Email address format is incorrect',
    'sp_email_max' => 'Email address cannot exceed 255 characters',
    'sp_email_invalid' => 'Email address is invalid',

    // Address
    'sp_address_min' => 'Address must be at least 5 characters',
    'sp_address_max' => 'Address cannot exceed 500 characters',
    'sp_address_no_html' => 'Address cannot contain HTML code',

    // Location
    'sp_location_invalid' => 'Selected location is invalid',

    // Services
    'sp_services_max' => 'Services offered cannot exceed 1000 characters',
    'sp_services_invalid_chars' => 'Services contain invalid characters',

    // Profile Image
    'sp_image_type' => 'Profile image must be an image file',
    'sp_image_mimes' => 'Profile image must be in JPG, JPEG, PNG, or WebP format',
    'sp_image_size' => 'Profile image cannot exceed 5MB',
    'sp_image_dimensions' => 'Image dimensions must be between 200x200 and 5000x5000 pixels',

    // Certification
    'sp_cert_file' => 'Certification must be a file',
    'sp_cert_mimes' => 'Certification must be in JPG, PNG, WebP, or PDF format',
    'sp_cert_size' => 'Certification file cannot exceed 10MB',

    // Category
    'sp_category_invalid' => 'Selected category/profession is invalid',
];
