<?php

return [
    // Service Provider Profile Update Validation Messages - Arabic

    // Business Name
    'sp_business_name_required' => 'اسم الشركة/النشاط مطلوب',
    'sp_business_name_min' => 'اسم الشركة يجب أن يكون على الأقل 3 أحرف',
    'sp_business_name_max' => 'اسم الشركة لا يمكن أن يتجاوز 255 حرف',
    'sp_business_name_invalid_chars' => 'اسم الشركة يحتوي على أحرف غير مسموح بها',

    // Bio / Description
    'sp_bio_min' => 'الوصف يجب أن يكون على الأقل 10 أحرف',
    'sp_bio_max' => 'الوصف لا يمكن أن يتجاوز 2000 حرف',
    'sp_bio_no_html' => 'الوصف لا يمكن أن يحتوي على أكواد HTML',

    // Experience
    'sp_experience_integer' => 'سنوات الخبرة يجب أن تكون رقم صحيح',
    'sp_experience_min' => 'سنوات الخبرة لا يمكن أن تكون سالبة',
    'sp_experience_max' => 'سنوات الخبرة لا يمكن أن تتجاوز 50 سنة',

    // Hourly Rate
    'sp_hourly_rate_numeric' => 'السعر بالساعة يجب أن يكون رقم',
    'sp_hourly_rate_min' => 'السعر بالساعة لا يمكن أن يكون سالب',
    'sp_hourly_rate_max' => 'السعر بالساعة لا يمكن أن يتجاوز 10000',
    'sp_hourly_rate_format' => 'السعر يجب أن يكون بصيغة صحيحة (رقمين بعد الفاصلة كحد أقصى)',

    // Phone
    'sp_phone_required' => 'رقم الهاتف مطلوب',
    'sp_phone_min' => 'رقم الهاتف يجب أن يكون 10 أرقام على الأقل',
    'sp_phone_max' => 'رقم الهاتف لا يمكن أن يتجاوز 20 رقم',
    'sp_phone_format' => 'رقم الهاتف بصيغة غير صحيحة (مثال: +15141234567 أو 514-123-4567)',
    'sp_phone_unique' => 'رقم الهاتف هذا مسجل بالفعل',

    // WhatsApp (إجباري)
    'sp_whatsapp_country_code_required' => 'كود الدولة مطلوب',
    'sp_whatsapp_country_code_in' => 'يرجى إدخال كود دولة صالح يبدأ بعلامة +',
    'sp_whatsapp_required' => 'رقم الواتساب مطلوب',
    'sp_whatsapp_min' => 'رقم الواتساب يجب أن يكون 10 أرقام على الأقل',
    'sp_whatsapp_max' => 'رقم الواتساب لا يمكن أن يتجاوز 15 رقم',
    'sp_whatsapp_format' => 'من فضلك أدخل رقم واتساب صالح (أرقام فقط، مسموح مسافات وشرطات)',

    // Email
    'sp_email_required' => 'البريد الإلكتروني مطلوب',
    'sp_email_format' => 'البريد الإلكتروني بصيغة غير صحيحة',
    'sp_email_max' => 'البريد الإلكتروني لا يمكن أن يتجاوز 255 حرف',
    'sp_email_invalid' => 'البريد الإلكتروني غير صالح',

    // Address
    'sp_address_min' => 'العنوان يجب أن يكون على الأقل 5 أحرف',
    'sp_address_max' => 'العنوان لا يمكن أن يتجاوز 500 حرف',
    'sp_address_english_only' => 'يحتوي العنوان على أحرف غير مدعومة.',

    // Location
    'sp_location_invalid' => 'الموقع المختار غير صالح',

    // Services
    'sp_services_max' => 'الخدمات المقدمة لا يمكن أن تتجاوز 1000 حرف',
    'sp_services_invalid_chars' => 'الخدمات تحتوي على أحرف غير مسموح بها',

    // Profile Image
    'sp_image_type' => 'صورة الملف الشخصي يجب أن تكون صورة',
    'sp_image_mimes' => 'صورة الملف الشخصي يجب أن تكون بصيغة JPG, JPEG, PNG, أو WebP',
    'sp_image_size' => 'صورة الملف الشخصي لا يمكن أن تتجاوز 5 ميجابايت',
    'sp_image_dimensions' => 'أبعاد الصورة يجب أن تكون بين 200x200 و 5000x5000 بكسل',

    // Certification
    'sp_cert_file' => 'الشهادة يجب أن تكون ملف',
    'sp_cert_mimes' => 'الشهادة يجب أن تكون بصيغة JPG, PNG, WebP, أو PDF',
    'sp_cert_size' => 'ملف الشهادة لا يمكن أن يتجاوز 10 ميجابايت',

    // Category
    'sp_category_invalid' => 'الفئة/الوظيفة المختارة غير صالحة',
];
