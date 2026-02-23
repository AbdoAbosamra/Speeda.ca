<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Speeda</title>
    <!-- استيراد خط Cairo للغة العربية لضمان الجمالية -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --text-color: #2c3e50;
            --bg-color: #f4f6f9;
            --card-bg: #ffffff;
            --section-border: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.7;
            color: var(--text-color);
            background: #f0f2f5;
            padding: 40px 20px;
            min-height: 100vh;
        }

        /* تنسيقات خاصة باللغة العربية */
        body[lang="ar"] {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
        }

        /* حاوية الصفحة */
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.02);
        }

        /* رأس الصفحة */
        .header {
            background: var(--primary-gradient);
            color: white;
            padding: 60px 40px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            font-weight: 700;
            line-height: 1.2;
        }

        .header h1 .icon {
            font-style: normal;
            margin-inline-end: 10px; /* يعمل مع RTL و LTR */
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        /* منطقة تبديل اللغة (للعرض فقط) */
        .lang-switcher {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .lang-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.4);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s;
        }

        .lang-btn.active, .lang-btn:hover {
            background: white;
            color: #764ba2;
            font-weight: bold;
        }

        /* المحتوى الرئيسي */
        .content {
            padding: 50px 50px;
        }

        @media (max-width: 768px) {
            .content {
                padding: 30px 25px;
            }
            .header {
                padding: 40px 20px;
            }
            .header h1 {
                font-size: 1.8em;
            }
        }

        .section {
            margin-bottom: 45px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--section-border);
        }

        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        /* عناوين الأقسام */
        .section-title {
            color: #667eea;
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px; /* مسافة ذكية تعمل في الاتجاهين */
        }

        .section-number {
            background: var(--primary-gradient);
            color: white;
            min-width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1em;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
        }

        .section-content {
            color: #555;
            font-size: 1.05em;
        }

        .section-content p {
            margin-bottom: 15px;
            text-align: justify; /* محاذاة النص */
        }

        /* القوائم الفرعية */
        .subsection {
            margin: 25px 0;
            padding-inline-start: 25px; /* مسافة ذكية */
            border-inline-start: 4px solid #667eea; /* حدود جانبية ذكية */
            background: #fafafa;
            padding: 15px;
            border-radius: 0 10px 10px 0;
        }

        [dir="ltr"] .subsection {
            border-radius: 10px 0 0 10px;
        }

        .subsection-title {
            font-weight: 700;
            color: #667eea;
            margin-bottom: 12px;
            font-size: 1.1em;
            display: block;
        }

        /* قوائم النقاط */
        .bullet-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .bullet-list li {
            padding: 8px 0;
            position: relative;
            padding-inline-start: 30px; /* مسافة للنقطة */
        }

        .bullet-list li::before {
            content: "•";
            color: #667eea;
            font-weight: bold;
            font-size: 1.5em;
            line-height: 1;
            position: absolute;
            inset-inline-start: 0; /* مكان النقطة ذكي */
            top: 5px;
        }

        /* القوائم المرقمة داخل القوائم (مثل 8.1) */
        .numbered-sublist {
            list-style: none;
            counter-reset: sub-section;
            margin-top: 10px;
        }
        
        .numbered-sublist li {
            counter-increment: sub-section;
            margin-bottom: 10px;
            position: relative;
            padding-inline-start: 35px;
        }

        .numbered-sublist li::before {
            content: counter(sub-section) ".";
            position: absolute;
            inset-inline-start: 0;
            font-weight: bold;
            color: #764ba2;
            font-size: 1em;
            top: 9px;
        }

        /* مربع التنبيه (مهم) */
        .highlight-box {
            background: #fff8e1;
            border-inline-start: 5px solid #ffc107;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            color: #795548;
        }

        .highlight-box strong {
            color: #d35400;
            display: block;
            margin-bottom: 5px;
        }

        /* صندوق المعلومات */
        .info-box {
            background: #e3f2fd;
            border-inline-start: 5px solid #2196f3;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .info-item {
            margin-bottom: 10px;
        }

        .info-term {
            font-weight: 700;
            color: #1565c0;
        }

        /* معلومات الاتصال */
        .contact-box {
            background: var(--primary-gradient);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-top: 50px;
        }

        .contact-box h3 {
            margin-bottom: 20px;
            font-size: 1.6em;
        }

        .contact-box p {
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .contact-box a {
            color: white;
            text-decoration: none;
            border-bottom: 1px dashed rgba(255,255,255,0.6);
            transition: border-color 0.3s;
        }

        .contact-box a:hover {
            border-bottom-color: white;
        }

        .last-updated {
            text-align: center;
            color: #888;
            font-size: 0.9em;
            margin-bottom: 30px;
            background: #f9f9f9;
            padding: 5px;
            border-radius: 5px;
            display: inline-block;
        }
        
        /* إخفاء/إظهار اللغات */
        .lang-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .lang-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <!-- أزرار تبديل اللغة (للغرض التجريبي) -->
            <div class="lang-switcher">
                <button class="lang-btn" onclick="switchLang('ar', this)" id="btn-ar">العربية</button>
                <button class="lang-btn" onclick="switchLang('en', this)" id="btn-en">English</button>
                <button class="lang-btn" onclick="switchLang('fr', this)" id="btn-fr">Français</button>
            </div>

            <h1 id="page-title">
                <span class="icon">📜</span> 
                <span class="title-text">شروط الخدمة</span>
            </h1>
            <p id="page-subtitle">اتفاقية منصة Speeda.CA</p>
        </div>

        <div class="content">
            <div style="text-align: center;">
                <p class="last-updated" id="last-updated">آخر تحديث: 23 أكتوبر 2025</p>
            </div>

            <!-- ================= المحتوى بالعربية ================= -->
            <div id="content-ar" class="lang-content active">
                
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        <span>التعريف والأطراف</span>
                    </h2>
                    <div class="section-content">
                        <p>تُشكّل هذه الشروط والأحكام ("الشروط") اتفاقية قانونية مُلزمة بينك وبين شركة Bnine General Trading Inc.، المالكة والمشغّلة لمنصة Speeda ("Speeda"، "نحن"، "لنا"، أو "خاصتنا")، ومقرّها في أوتاوا، أونتاريو، كندا. تُنظّم هذه الشروط استخدامك لموقع www.Speeda.ca وأي خدمات رقمية ذات صلة (يُشار إليها مجتمعة بـ "المنصة").</p>
                        <div class="info-box">
                            <div class="info-item"><span class="info-term">العميل:</span> أي فرد أو شركة تبحث عن مزوّد خدمة عبر المنصة.</div>
                            <div class="info-item"><span class="info-term">مزوّد الخدمة:</span> أي فرد أو شركة تُقدّم خدمات بشكل مستقل عبر المنصة.</div>
                            <div class="info-item"><span class="info-term">المستخدم:</span> يشمل كلًّا من العملاء ومزوّدي الخدمات.</div>
                            <div class="info-item"><span class="info-term">اتفاقية الخدمة:</span> العقد أو التفاهم المباشر بين العميل ومزوّد الخدمة بشأن خدمة محددة.</div>
                            <div class="info-item"><span class="info-term">مزود خدمات الدفع (PSP):</span> أي جهة خارجية لمعالجة المدفوعات (مثل PayPal أو التحويل الإلكتروني أو غيرها).</div>
                        </div>
                        <p>من خلال الوصول إلى المنصة أو استخدامها، فإنك توافق على هذه الشروط وعلى سياسة الخصوصية الخاصة بنا. وفي حال عدم موافقتك، يجب عليك التوقف فورًا عن استخدام المنصة.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        <span>الأهلية وإنشاء الحساب</span>
                    </h2>
                    <div class="section-content">
                        <p>يجب أن يكون المستخدمون قد أتمّوا 18 عامًا على الأقل وأن يكونوا مؤهلين قانونيًا لإبرام العقود. ويُطلب منهم تقديم معلومات دقيقة ومُحدّثة، كما يتحمّلون مسؤولية الحفاظ على سرّية بيانات الدخول وجميع أنشطة الحساب. تحتفظ Speeda بحق تعليق أو رفض أو إنهاء أي حساب لحماية المستخدمين أو الامتثال للقانون.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        <span>طبيعة دور Speeda</span>
                    </h2>
                    <div class="section-content">
                        <div class="highlight-box">
                            <strong>مهم:</strong> تعمل Speeda كوسيط تكنولوجي فقط يربط بين العملاء ومزوّدي الخدمات. ولا تُقدّم Speeda الخدمات بنفسها، ولا توظّف مزوّدي الخدمات، ولا تحدد الأسعار، ولا تُشرف على جودة الخدمات أو توقيتها أو نتائجها.
                        </div>
                        <p>ولا يُنشئ أي بند في هذه الشروط علاقة توظيف أو وكالة أو شراكة بين Speeda وأي مستخدم.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">4</span>
                        <span>استخدام المنصة واتفاقيات الخدمة</span>
                    </h2>
                    <div class="section-content">
                        <p>يختار العملاء مزوّدي الخدمات بحرّية استنادًا إلى المعلومات المتاحة على المنصة. وبمجرد توصّل العميل ومزوّد الخدمة إلى اتفاق، تُبرم اتفاقية الخدمة حصريًا بينهما. ولا تُعتبر Speeda طرفًا في أي اتفاقية خدمة، كما لا تتحمّل أي مسؤولية قانونية أو مالية عن أدائها أو نتائجها أو النزاعات المتعلقة بها.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        <span>الاشتراكات والرسوم والمدفوعات والإلغاء</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>تعمل Speeda وفق نظام اشتراك شهري يمنح المستخدمين إمكانية الوصول إلى ميزات وخدمات المنصة.</li>
                            <li>جميع الرسوم غير قابلة للاسترداد كليًا أو جزئيًا.</li>
                            <li>يمكن للمستخدمين إلغاء اشتراكهم في أي وقت، ويُصبح الإلغاء ساريًا من بداية دورة الفوترة التالية.</li>
                            <li>لا تُرد أي مبالغ عن الفترات غير المستخدمة.</li>
                            <li>يمكن سداد المدفوعات عبر التحويل الإلكتروني أو PayPal أو أي مزود دفع آخر تدعمه Speeda.</li>
                            <li>باستخدام أي مزود دفع، يوافق المستخدم على شروطه ويُقرّ بأن Speeda ليست بنكًا ولا وكيل ضمان.</li>
                            <li>قد تُضاف الضرائب المطبقة (مثل HST/GST/PST) حسب القانون.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        <span>التزامات مزوّدي الخدمات</span>
                    </h2>
                    <div class="section-content">
                        <p>يُقرّ مزوّدو الخدمات ويضمنون أنهم:</p>
                        <ul class="bullet-list">
                            <li>يعملون بصورة قانونية ومستقلة ضمن نطاقهم القضائي.</li>
                            <li>يحملون التراخيص والتصاريح اللازمة.</li>
                            <li>يحتفظون بالتأمينات المطلوبة.</li>
                            <li>يُقدّمون الخدمات باحترافية وأمان وبشكل قانوني.</li>
                            <li>يتحمّلون كامل المسؤولية عن موظفيهم أو مساعدينهم أو متعاقدين الفرعيين.</li>
                        </ul>
                        <div class="highlight-box">
                            <strong>تنبيه:</strong> لا تتحقق Speeda من مؤهلات مزوّدي الخدمات أو تراخيصهم. ويتعيّن على العملاء توخّي الحذر قبل التعاقد.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        <span>التزامات العملاء</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>تقديم أوصاف دقيقة للخدمات المطلوبة.</li>
                            <li>ضمان الوصول الآمن والمشروع إلى موقع الخدمة.</li>
                            <li>الامتناع عن طلب خدمات غير قانونية أو خطرة.</li>
                            <li>الالتزام بسداد المدفوعات المتفق عليها مباشرة أو عبر مزود الدفع المختار.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        <span>المراجعات والمحتوى الذي ينشئه المستخدم</span>
                    </h2>
                    <div class="section-content">
                        <p>يجوز للمستخدمين نشر مراجعات أو تقييمات أو صور أو محتوى آخر ("محتوى المستخدم"). وبقيامهم بذلك، يمنحون Speeda ترخيصًا عالميًا غير حصري وخاليًا من الرسوم لاستخدام هذا المحتوى لتشغيل المنصة وترويجها وتحسينها.</p>
                        <p>يُقرّ المستخدمون بأن محتواهم قانوني ولا ينتهك حقوق الآخرين، وتحتفظ Speeda بالحق في إزالة أي محتوى غير لائق أو تعليق الحسابات المسيئة.</p>
                    </div>
                </div>

                <!-- القسم 8 مكرر - نظام التقييمات -->
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        <span>نظام التقييمات والتعليقات</span>
                    </h2>
                    <div class="section-content">
                        <p>توفر منصة Speeda خاصية التقييمات والتعليقات كوسيلة تواصل بين المستخدمين فقط.</p>
                        <ul class="numbered-sublist">
                            <li>يتحمّل المستخدم وحده كامل المسؤولية القانونية عن أي تقييم أو تعليق أو محتوى ينشره عبر المنصة، سواء كان مكتوبًا أو مرئيًا أو بأي صيغة أخرى.</li>
                            <li>لا تتحمّل Speeda أي مسؤولية قانونية أو مدنية أو جزائية عن محتوى التقييمات أو التعليقات أو دقتها أو آثارها أو أي أضرار قد تنشأ عنها، ويقع عبء التحقق وصحة المحتوى على عاتق ناشره فقط.</li>
                            <li>يُحظر نشر أي تعليقات أو تقييمات تتضمن، على سبيل المثال لا الحصر: إساءة أو تشهيرًا أو ألفاظًا نابية أو محتوى غير لائق، معلومات كاذبة أو مضلّلة، تهديدات أو تحريضًا أو كراهية، أو أي انتهاك للقوانين المعمول بها أو لحقوق الغير.</li>
                            <li>تحتفظ Speeda بالحق المطلق، دون أي التزام أو إشعار مسبق، في: حذف أو تعديل أو إخفاء أي تعليق أو تقييم مخالف، تعليق أو إنهاء حساب المستخدم المخالف، أو الاحتفاظ بنسخ من المحتوى لأغراض قانونية أو تنظيمية.</li>
                            <li>يوافق المستخدم صراحةً على أن لـ Speeda الحق في اتخاذ الإجراءات القانونية المناسبة، بما في ذلك ملاحقة المستخدم قضائيًا أو التعاون مع الجهات المختصة، في حال نشر أي تعليق أو تقييم مخالف للقانون أو مُسيء أو يُلحق ضررًا بالمنصة أو بمستخدميها أو بأي طرف ثالث.</li>
                            <li>لا يُعتبر السماح بنشر التقييمات أو التعليقات موافقةً أو تبنّيًا أو تصديقًا من Speeda على محتواها بأي شكل من الأشكال.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        <span>الاستخدام المقبول</span>
                    </h2>
                    <div class="section-content">
                        <p>يُحظر على المستخدمين:</p>
                        <ul class="bullet-list">
                            <li>انتهاك القوانين أو حقوق الغير.</li>
                            <li>نشر أو طلب محتوى غير قانوني أو مسيء أو خطير.</li>
                            <li>انتحال هوية الآخرين أو تقديم مؤهلات مضلّلة.</li>
                            <li>التحايل على رسوم Speeda أو نظام الدفع الخاص بها.</li>
                            <li>تحميل برمجيات ضارة أو استخدام تقنيات جمع بيانات تلقائية.</li>
                        </ul>
                        <p>يجوز لـ Speeda اتخاذ إجراءات تقنية أو قانونية لمنع أي نشاط محظور.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        <span>الملكية الفكرية</span>
                    </h2>
                    <div class="section-content">
                        <p>جميع محتويات المنصة (النصوص، التصاميم، البرامج، الشعارات، الرسومات، إلخ) مملوكة أو مرخّصة لـ Speeda ومحمية بموجب القوانين المعمول بها. ولا يجوز للمستخدمين نسخ أو تعديل أو إعادة توزيع أي جزء منها دون موافقة كتابية مسبقة.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        <span>الروابط والخدمات التابعة لجهات خارجية</span>
                    </h2>
                    <div class="section-content">
                        <p>قد تحتوي المنصة على روابط أو تكاملات مع خدمات أطراف ثالثة (مثل مزوّدي الدفع أو التحليلات أو الخرائط). ولا تتحمّل Speeda أي مسؤولية عن تلك الخدمات أو محتواها، ويخضع استخدامها لشروطها وسياسات الخصوصية الخاصة بها.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        <span>إخلاء المسؤولية عن الضمانات</span>
                    </h2>
                    <div class="section-content">
                        <p>تُقدَّم المنصة "كما هي" و"حسب التوافر"، دون أي ضمانات صريحة أو ضمنية. لا تضمن Speeda دقة المعلومات أو توافر المنصة أو ملاءمتها لأي غرض. كما لا تتحمّل مسؤولية جودة أو توقيت أو نتائج الخدمات المقدَّمة من مزوّدي الخدمات.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        <span>إخلاء المسؤولية الكاملة</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>تعمل Speeda فقط كوسيط تكنولوجي بين العملاء ومزوّدي الخدمات، ولا تُقدّم أي خدمات بنفسها، ولا تُعد طرفًا في أي اتفاق أو معاملة بين المستخدمين.</li>
                            <li>تُخلي Speeda مسؤوليتها عن أي أضرار مباشرة أو غير مباشرة أو مالية أو معنوية ناجمة عن: أي اتفاق أو نزاع بين العميل ومزوّد الخدمة، أداء الخدمة أو تأخرها أو جودتها، أي بيانات أو محتوى أو تواصل بين المستخدمين، أو أي عطل تقني أو صيانة أو توقف مؤقت أو دائم للمنصة.</li>
                            <li>لا تتحمّل Speeda أي التزامات مالية أو قانونية تجاه أي مستخدم أو طرف ثالث تحت أي نظرية قانونية.</li>
                            <li>يقرّ المستخدمون بأنهم يستخدمون المنصة على مسؤوليتهم الخاصة، وأن دور Speeda يقتصر على تسهيل التواصل.</li>
                            <li>تحتفظ Speeda بالحق الكامل في تعليق أو إيقاف أو إنهاء المنصة في أي وقت ولأي سبب دون إشعار مسبق أو تعويض.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        <span>التعويض</span>
                    </h2>
                    <div class="section-content">
                        <p>يوافق المستخدمون على تعويض وحماية Speeda وموظفيها ووكلائها من أي مطالبات أو خسائر أو نفقات ناتجة عن استخدامهم للمنصة أو مخالفتهم لهذه الشروط أو نزاعاتهم مع مستخدمين آخرين.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        <span>القانون الواجب التطبيق والاختصاص القضائي</span>
                    </h2>
                    <div class="section-content">
                        <p>تخضع هذه الشروط لقوانين مقاطعة أونتاريو والقوانين الفيدرالية الكندية السارية. وتتمتع محاكم أوتاوا، أونتاريو بالاختصاص الحصري في أي نزاعات قانونية متعلقة بهذه الشروط.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">16</span>
                        <span>الإشعارات والتواصل</span>
                    </h2>
                    <div class="section-content">
                        <p>يوافق المستخدمون على تلقي الإشعارات إلكترونيًا عبر البريد الإلكتروني أو داخل المنصة، وتُعدّ الإجراءات والموافقات الإلكترونية توقيعات قانونية مُلزمة.</p>
                        <p><strong>عنوان البريد الرسمي:</strong> support@speeda.ca</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">17</span>
                        <span>التعديلات</span>
                    </h2>
                    <div class="section-content">
                        <p>يجوز لـ Speeda تعديل هذه الشروط أو تحديث المنصة في أي وقت. ويُعدّ استمرار استخدام المنصة بعد نشر التحديثات موافقة على الشروط المعدّلة.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">18</span>
                        <span>اللغات</span>
                    </h2>
                    <div class="section-content">
                        <p>تتوفر المنصة باللغات الإنجليزية والفرنسية والعربية. وفي حال وجود أي تعارض بين النسخ، تكون النسخة الإنجليزية هي المعتمدة ما لم ينص القانون على خلاف ذلك.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">19</span>
                        <span>أحكام عامة</span>
                    </h2>
                    <div class="section-content">
                        <p>تُشكّل هذه الشروط وسياسة الخصوصية كامل الاتفاق بين المستخدمين وSpeeda. لا يُعتبر أي تنازل عن بندٍ ما تنازلاً مستمرًا عن غيره. إذا ثبت بطلان أي بند، يُعدّل بالحد الأدنى اللازم مع بقاء باقي البنود سارية. لا يجوز للمستخدمين التنازل عن هذه الشروط دون موافقة كتابية، بينما يجوز لـ Speeda نقلها إلى كيان تابع أو خلف قانوني.</p>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>معلومات الاتصال</h3>
                    <p><strong>Bnine General Trading Inc. (Speeda)</strong></p>
                    <p>📍 أوتاوا، أونتاريو، كندا</p>
                    <p>البريد الإلكتروني: <a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                    <p>الموقع الإلكتروني: <a href="https://www.Speeda.ca" target="_blank">www.Speeda.ca</a></p>
                </div>

            </div>

            <!-- ================= المحتوى بالإنجليزية ================= -->
            <div id="content-en" class="lang-content">
                
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        <span>Definitions and Parties</span>
                    </h2>
                    <div class="section-content">
                        <p>These Terms and Conditions ("Terms") constitute a legally binding agreement between you and Bnine General Trading Inc., the owner and operator of the Speeda platform ("Speeda", "we", "us", or "our"), headquartered in Ottawa, Ontario, Canada. These Terms govern your use of the website www.Speeda.ca and any related digital services (collectively referred to as the "Platform").</p>
                        <div class="info-box">
                            <div class="info-item"><span class="info-term">Client:</span> Any individual or company seeking a service provider via the Platform.</div>
                            <div class="info-item"><span class="info-term">Service Provider:</span> Any individual or company offering services independently via the Platform.</div>
                            <div class="info-item"><span class="info-term">User:</span> Includes both Clients and Service Providers.</div>
                            <div class="info-item"><span class="info-term">Service Agreement:</span> The direct contract or understanding between the Client and the Service Provider regarding a specific service.</div>
                            <div class="info-item"><span class="info-term">Payment Service Provider (PSP):</span> Any third-party entity for payment processing (e.g., PayPal, E-Transfer, etc.).</div>
                        </div>
                        <p>By accessing or using the Platform, you agree to these Terms and our Privacy Policy. If you do not agree, you must immediately stop using the Platform.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        <span>Eligibility and Account Creation</span>
                    </h2>
                    <div class="section-content">
                        <p>Users must be at least 18 years old and legally eligible to enter into contracts. They are required to provide accurate and up-to-date information, and are responsible for maintaining the confidentiality of their login credentials and all account activities. Speeda reserves the right to suspend, refuse, or terminate any account to protect users or comply with the law.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        <span>Nature of Speeda's Role</span>
                    </h2>
                    <div class="section-content">
                        <div class="highlight-box">
                            <strong>Important:</strong> Speeda acts solely as a technology intermediary connecting Clients and Service Providers. Speeda does not provide the services itself, does not employ Service Providers, does not set prices, and does not supervise the quality, timing, or results of the services.
                        </div>
                        <p>No provision in these Terms creates an employment, agency, or partnership relationship between Speeda and any User.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">4</span>
                        <span>Platform Use and Service Agreements</span>
                    </h2>
                    <div class="section-content">
                        <p>Clients choose Service Providers freely based on information available on the Platform. Once a Client and a Service Provider reach an agreement, a Service Agreement is concluded exclusively between them. Speeda is not a party to any Service Agreement and assumes no legal or financial liability for its performance, results, or related disputes.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        <span>Subscriptions, Fees, Payments, and Cancellation</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>Speeda operates on a monthly subscription system granting users access to Platform features and services.</li>
                            <li>All fees are non-refundable, in whole or in part.</li>
                            <li>Users may cancel their subscription at any time; cancellation becomes effective at the start of the next billing cycle.</li>
                            <li>No refunds are issued for unused periods.</li>
                            <li>Payments can be made via E-Transfer, PayPal, or any other payment provider supported by Speeda.</li>
                            <li>By using any payment provider, the User agrees to its terms and acknowledges that Speeda is not a bank or escrow agent.</li>
                            <li>Applicable taxes (such as HST/GST/PST) may be added as required by law.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        <span>Service Provider Obligations</span>
                    </h2>
                    <div class="section-content">
                        <p>Service Providers acknowledge and warrant that they:</p>
                        <ul class="bullet-list">
                            <li>Operate legally and independently within their jurisdiction.</li>
                            <li>Hold necessary licenses and permits.</li>
                            <li>Maintain required insurance.</li>
                            <li>Provide services professionally, safely, and legally.</li>
                            <li>Assume full responsibility for their employees, assistants, or subcontractors.</li>
                        </ul>
                        <div class="highlight-box">
                            <strong>Notice:</strong> Speeda does not verify the qualifications or licenses of Service Providers. Clients should exercise caution before contracting.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        <span>Client Obligations</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>Provide accurate descriptions of requested services.</li>
                            <li>Ensure safe and lawful access to the service location.</li>
                            <li>Refrain from requesting illegal or dangerous services.</li>
                            <li>Commit to paying agreed amounts directly or via the selected payment provider.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        <span>Reviews and User-Generated Content</span>
                    </h2>
                    <div class="section-content">
                        <p>Users may post reviews, ratings, photos, or other content ("User Content"). By doing so, they grant Speeda a worldwide, non-exclusive, royalty-free license to use this content to operate, promote, and improve the Platform.</p>
                        <p>Users acknowledge that their content is lawful and does not infringe on the rights of others. Speeda reserves the right to remove any inappropriate content or suspend offending accounts.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        <span>Rating and Comment System</span>
                    </h2>
                    <div class="section-content">
                        <p>Speeda provides the rating and comment feature solely as a means of communication between Users.</p>
                        <ul class="numbered-sublist">
                            <li>The User alone bears full legal responsibility for any rating, comment, or content published via the Platform, whether written, visual, or in any other format.</li>
                            <li>Speeda assumes no legal, civil, or criminal liability for the content of ratings or comments, their accuracy, their effects, or any damages arising from them; the burden of verification and content accuracy lies solely with the publisher.</li>
                            <li>It is prohibited to post any comments or ratings containing, but not limited to: abuse, defamation, profanity, inappropriate content, false or misleading information, threats, incitement, hatred, or any violation of applicable laws or third-party rights.</li>
                            <li>Speeda reserves the absolute right, without any obligation or prior notice, to: delete, modify, or hide any violating comment or rating, suspend or terminate the offending user's account, and retain copies of content for legal or regulatory purposes.</li>
                            <li>The User explicitly agrees that Speeda has the right to take appropriate legal action, including suing the User or cooperating with authorities, if any comment or rating violates the law, is offensive, or causes harm to the Platform, its users, or any third party.</li>
                            <li>Allowing the posting of ratings or comments does not constitute Speeda's approval, adoption, or endorsement of their content in any way.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        <span>Acceptable Use</span>
                    </h2>
                    <div class="section-content">
                        <p>Users are prohibited from:</p>
                        <ul class="bullet-list">
                            <li>Violating laws or third-party rights.</li>
                            <li>Posting or requesting illegal, offensive, or dangerous content.</li>
                            <li>Impersonating others or providing misleading qualifications.</li>
                            <li>Circumventing Speeda's fees or payment system.</li>
                            <li>Uploading malicious software or using automatic data collection techniques.</li>
                        </ul>
                        <p>Speeda may take technical or legal action to prevent any prohibited activity.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        <span>Intellectual Property</span>
                    </h2>
                    <div class="section-content">
                        <p>All Platform content (texts, designs, software, logos, graphics, etc.) is owned or licensed by Speeda and protected by applicable laws. Users may not copy, modify, or redistribute any part of it without prior written consent.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        <span>Third-Party Links and Services</span>
                    </h2>
                    <div class="section-content">
                        <p>The Platform may contain links or integrations with third-party services (such as payment providers, analytics, or maps). Speeda assumes no responsibility for those services or their content; their use is subject to their own terms and privacy policies.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        <span>Disclaimer of Warranties</span>
                    </h2>
                    <div class="section-content">
                        <p>The Platform is provided "as is" and "as available," without any express or implied warranties. Speeda does not guarantee the accuracy of information, the availability of the Platform, or its fitness for any purpose. Speeda also assumes no responsibility for the quality, timing, or results of services provided by Service Providers.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        <span>General Disclaimer</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>Speeda acts only as a technology intermediary between Clients and Service Providers, does not provide any services itself, and is not a party to any agreement or transaction between Users.</li>
                            <li>Speeda disclaims liability for any direct, indirect, financial, or moral damages arising from: any agreement or dispute between Client and Service Provider, the performance, delay, or quality of the service, any data or content or communication between Users, or any technical fault, maintenance, or temporary or permanent suspension of the Platform.</li>
                            <li>Speeda assumes no financial or legal obligations to any User or third party under any legal theory.</li>
                            <li>Users acknowledge that they use the Platform at their own risk and that Speeda's role is limited to facilitating communication.</li>
                            <li>Speeda reserves the full right to suspend, stop, or terminate the Platform at any time and for any reason without prior notice or compensation.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        <span>Indemnification</span>
                    </h2>
                    <div class="section-content">
                        <p>Users agree to indemnify and hold harmless Speeda, its employees, and agents from any claims, losses, or expenses resulting from their use of the Platform, violation of these Terms, or disputes with other users.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        <span>Governing Law and Jurisdiction</span>
                    </h2>
                    <div class="section-content">
                        <p>These Terms are subject to the laws of the Province of Ontario and the applicable federal laws of Canada. The courts of Ottawa, Ontario have exclusive jurisdiction over any legal disputes related to these Terms.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">16</span>
                        <span>Notices and Communication</span>
                    </h2>
                    <div class="section-content">
                        <p>Users agree to receive notifications electronically via email or within the Platform. Electronic actions and consents are considered legally binding signatures.</p>
                        <p><strong>Official Email Address:</strong> support@speeda.ca</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">17</span>
                        <span>Amendments</span>
                    </h2>
                    <div class="section-content">
                        <p>Speeda may amend these Terms or update the Platform at any time. Continued use of the Platform after posting updates constitutes acceptance of the modified Terms.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">18</span>
                        <span>Languages</span>
                    </h2>
                    <div class="section-content">
                        <p>The Platform is available in English, French, and Arabic. In case of any conflict between versions, the English version shall prevail unless the law provides otherwise.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">19</span>
                        <span>General Provisions</span>
                    </h2>
                    <div class="section-content">
                        <p>These Terms and the Privacy Policy constitute the entire agreement between Users and Speeda. No waiver of any provision shall be a continuing waiver of any other. If any provision is found invalid, it shall be amended to the minimum extent necessary with the remaining provisions staying in effect. Users may not waive these Terms without written consent, while Speeda may assign them to an affiliate or legal successor.</p>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>Contact Information</h3>
                    <p><strong>Bnine General Trading Inc. (Speeda)</strong></p>
                    <p>📍 Ottawa, Ontario, Canada</p>
                    <p>Email: <a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                    <p>Website: <a href="https://www.Speeda.ca" target="_blank">www.Speeda.ca</a></p>
                </div>

            </div>

            <!-- ================= المحتوى بالفرنسية ================= -->
            <div id="content-fr" class="lang-content">
                
                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        <span>Définitions et Parties</span>
                    </h2>
                    <div class="section-content">
                        <p>Les présentes Conditions Générales ("Conditions") constituent un accord juridique contraignant entre vous et Bnine General Trading Inc., propriétaire et exploitant de la plateforme Speeda ("Speeda", "nous", "notre" ou "nos"), dont le siège est à Ottawa, Ontario, Canada. Ces Conditions régissent votre utilisation du site www.Speeda.ca et de tous services numériques connexes (collectivement appelés la "Plateforme").</p>
                        <div class="info-box">
                            <div class="info-item"><span class="info-term">Client :</span> Toute personne physique ou morale recherchant un prestataire de services via la Plateforme.</div>
                            <div class="info-item"><span class="info-term">Prestataire de services :</span> Toute personne physique ou morale offrant des services de manière indépendante via la Plateforme.</div>
                            <div class="info-item"><span class="info-term">Utilisateur :</span> Inclut à la fois les Clients et les Prestataires de services.</div>
                            <div class="info-item"><span class="info-term">Accord de service :</span> Le contrat direct ou l'entente entre le Client et le Prestataire de services concernant un service spécifique.</div>
                            <div class="info-item"><span class="info-term">Prestataire de services de paiement (PSP) :</span> Toute tierce partie pour le traitement des paiements (ex: PayPal, virement électronique, etc.).</div>
                        </div>
                        <p>En accédant à la Plateforme ou en l'utilisant, vous acceptez ces Conditions et notre Politique de Confidentialité. Si vous n'acceptez pas ces Conditions, vous devez cesser immédiatement d'utiliser la Plateforme.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        <span>Éligibilité et Création de Compte</span>
                    </h2>
                    <div class="section-content">
                        <p>Les utilisateurs doivent avoir au moins 18 ans et être juridiquement capables de conclure des contrats. Ils sont tenus de fournir des informations exactes et à jour, et sont responsables de maintenir la confidentialité de leurs identifiants de connexion et de toutes les activités du compte. Speeda se réserve le droit de suspendre, refuser ou résilier tout compte pour protéger les utilisateurs ou se conformer à la loi.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        <span>Nature du Rôle de Speeda</span>
                    </h2>
                    <div class="section-content">
                        <div class="highlight-box">
                            <strong>Important :</strong> Speeda agit uniquement en tant qu'intermédiaire technologique mettant en relation les Clients et les Prestataires de services. Speeda ne fournit pas les services elle-même, n'emploie pas les Prestataires de services, ne fixe pas les prix et ne supervise pas la qualité, le calendrier ou les résultats des services.
                        </div>
                        <p>Aucune disposition de ces Conditions ne crée une relation de travail, d'agence ou de partenariat entre Speeda et tout Utilisateur.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">4</span>
                        <span>Utilisation de la Plateforme et Accords de Service</span>
                    </h2>
                    <div class="section-content">
                        <p>Les Clients choisissent librement les Prestataires de services en fonction des informations disponibles sur la Plateforme. Une fois qu'un Client et un Prestataire de services parviennent à un accord, l'Accord de Service est conclu exclusivement entre eux. Speeda n'est pas partie à un Accord de Service et n'assume aucune responsabilité juridique ou financière pour son exécution, ses résultats ou les litiges connexes.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        <span>Abonnements, Frais, Paiements et Annulation</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>Speeda fonctionne sur un système d'abonnement mensuel donnant aux utilisateurs accès aux fonctionnalités et services de la Plateforme.</li>
                            <li>Tous les frais sont non remboursables, en tout ou en partie.</li>
                            <li>Les utilisateurs peuvent annuler leur abonnement à tout moment ; l'annulation prend effet au début du prochain cycle de facturation.</li>
                            <li>Aucun remboursement n'est émis pour les périodes non utilisées.</li>
                            <li>Les paiements peuvent être effectués par virement électronique, PayPal ou tout autre fournisseur de paiement pris en charge par Speeda.</li>
                            <li>En utilisant un fournisseur de paiement, l'Utilisateur accepte ses conditions et reconnaît que Speeda n'est pas une banque ou un agent de séquestre.</li>
                            <li>Les taxes applicables (telles que TVH/TPS/TVP) peuvent être ajoutées conformément à la loi.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        <span>Obligations des Prestataires de Services</span>
                    </h2>
                    <div class="section-content">
                        <p>Les Prestataires de services reconnaissent et garantissent qu'ils :</p>
                        <ul class="bullet-list">
                            <li>Agissent légalement et de manière indépendante dans leur juridiction.</li>
                            <li>Détiennent les licences et permis nécessaires.</li>
                            <li>Maintiennent les assurances requises.</li>
                            <li>Fournissent les services de manière professionnelle, sûre et légale.</li>
                            <li>Assument l'entière responsabilité de leurs employés, assistants ou sous-traitants.</li>
                        </ul>
                        <div class="highlight-box">
                            <strong>Remarque :</strong> Speeda ne vérifie pas les qualifications ou les licences des Prestataires de services. Les Clients doivent faire preuve de prudence avant de contracter.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        <span>Obligations des Clients</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>Fournir des descriptions précises des services demandés.</li>
                            <li>Garantir un accès sûr et légal au lieu du service.</li>
                            <li>S'abstenir de demander des services illégaux ou dangereux.</li>
                            <li>S'engager à payer les montants convenus directement ou via le fournisseur de paiement choisi.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        <span>Avis et Contenu Généré par l'Utilisateur</span>
                    </h2>
                    <div class="section-content">
                        <p>Les utilisateurs peuvent publier des avis, des évaluations, des photos ou d'autres contenus ("Contenu de l'Utilisateur"). Ce faisant, ils accordent à Speeda une licence mondiale, non exclusive et gratuite d'utiliser ce contenu pour exploiter, promouvoir et améliorer la Plateforme.</p>
                        <p>Les utilisateurs reconnaissent que leur contenu est licite et ne porte pas atteinte aux droits d'autrui. Speeda se réserve le droit de supprimer tout contenu inapproprié ou de suspendre les comptes contrevenants.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        <span>Système de Notation et de Commentaires</span>
                    </h2>
                    <div class="section-content">
                        <p>Speeda fournit la fonctionnalité de notation et de commentaires uniquement comme moyen de communication entre les Utilisateurs.</p>
                        <ul class="numbered-sublist">
                            <li>L'Utilisateur porte seul l'entière responsabilité juridique de toute note, commentaire ou contenu publié via la Plateforme, qu'il soit écrit, visuel ou sous tout autre format.</li>
                            <li>Speeda n'assume aucune responsabilité juridique, civile ou pénale pour le contenu des notes ou commentaires, leur exactitude, leurs effets ou tout dommage en découlant ; la charge de la vérification et de l'exactitude du contenu incombe uniquement à l'éditeur.</li>
                            <li>Il est interdit de publier des commentaires ou notes contenant, notamment : des abus, de la diffamation, des grossièretés, un contenu inapproprié, des informations fausses ou trompeuses, des menaces, de l'incitation, de la haine ou toute violation des lois applicables ou des droits de tiers.</li>
                            <li>Speeda se réserve le droit absolu, sans obligation ni préavis, de : supprimer, modifier ou masquer tout commentaire ou note contrevenant, suspendre ou résilier le compte de l'utilisateur contrevenant, et conserver des copies du contenu à des fins juridiques ou réglementaires.</li>
                            <li>L'Utilisateur convient expressément que Speeda a le droit de prendre toute mesure juridique appropriée, y compris poursuivre l'Utilisateur en justice ou coopérer avec les autorités, si tout commentaire ou note enfreint la loi, est offensant ou cause des préjudices à la Plateforme, à ses utilisateurs ou à un tiers.</li>
                            <li>Le fait d'autoriser la publication de notes ou de commentaires ne constitue pas une approbation, une adoption ou une endorsement par Speeda de leur contenu de quelque manière que ce soit.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        <span>Usage Acceptable</span>
                    </h2>
                    <div class="section-content">
                        <p>Il est interdit aux Utilisateurs de :</p>
                        <ul class="bullet-list">
                            <li>Violenter les lois ou les droits d'autrui.</li>
                            <li>Publier ou demander du contenu illégal, offensant ou dangereux.</li>
                            <li>Usurper l'identité d'autrui ou fournir des qualifications trompeuses.</li>
                            <li>Contourner les frais de Speeda ou son système de paiement.</li>
                            <li>Télécharger des logiciels malveillants ou utiliser des techniques de collecte de données automatiques.</li>
                        </ul>
                        <p>Speeda peut prendre des mesures techniques ou juridiques pour empêcher toute activité interdite.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        <span>Propriété Intellectuelle</span>
                    </h2>
                    <div class="section-content">
                        <p>Tout le contenu de la Plateforme (textes, designs, logiciels, logos, graphiques, etc.) est la propriété de Speeda ou sous licence par elle, et est protégé par les lois applicables. Les utilisateurs ne peuvent pas copier, modifier ou redistribuer une partie de celui-ci sans consentement écrit préalable.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        <span>Liens et Services Tiers</span>
                    </h2>
                    <div class="section-content">
                        <p>La Plateforme peut contenir des liens ou des intégrations avec des services tiers (tels que des fournisseurs de paiement, des analyses ou des cartes). Speeda n'assume aucune responsabilité pour ces services ou leur contenu ; leur utilisation est soumise à leurs propres conditions et politiques de confidentialité.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        <span>Exonération de Garantie</span>
                    </h2>
                    <div class="section-content">
                        <p>La Plateforme est fournie "telle quelle" et "selon disponibilité", sans garantie expresse ou implicite. Speeda ne garantit pas l'exactitude des informations, la disponibilité de la Plateforme ou sa pertinence pour un usage particulier. Speeda n'assume également aucune responsabilité pour la qualité, le calendrier ou les résultats des services fournis par les Prestataires de services.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        <span>Exonération de Responsabilité Générale</span>
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>Speeda agit uniquement en tant qu'intermédiaire technologique entre les Clients et les Prestataires de services, ne fournit aucun service elle-même et n'est partie à aucun accord ou transaction entre les Utilisateurs.</li>
                            <li>Speeda décline toute responsabilité pour les dommages directs, indirects, financiers ou moraux résultant de : tout accord ou litige entre le Client et le Prestataire de services, de l'exécution, du retard ou de la qualité du service, de toute donnée ou contenu ou communication entre les Utilisateurs, ou de tout dysfonctionnement technique, maintenance ou suspension temporaire ou définitive de la Plateforme.</li>
                            <li>Speeda n'assume aucune obligation financière ou juridique envers tout Utilisateur ou tiers en vertu de toute théorie juridique.</li>
                            <li>Les utilisateurs reconnaissent qu'ils utilisent la Plateforme à leurs propres risques et que le rôle de Speeda se limite à faciliter la communication.</li>
                            <li>Speeda se réserve le droit complet de suspendre, arrêter ou résilier la Plateforme à tout moment et pour toute raison sans préavis ni compensation.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        <span>Indemnisation</span>
                    </h2>
                    <div class="section-content">
                        <p>Les utilisateurs conviennent d'indemniser et de dégager Speeda, ses employés et ses agents de toute réclamation, perte ou dépense résultant de leur utilisation de la Plateforme, de la violation des présentes Conditions ou de litiges avec d'autres utilisateurs.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        <span>Loi Applicable et Juridiction</span>
                    </h2>
                    <div class="section-content">
                        <p>Les présentes Conditions sont régies par les lois de la province de l'Ontario et les lois fédérales applicables du Canada. Les tribunaux d'Ottawa, Ontario ont la compétence exclusive pour tout litige juridique lié aux présentes Conditions.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">16</span>
                        <span>Notifications et Communication</span>
                    </h2>
                    <div class="section-content">
                        <p>Les utilisateurs acceptent de recevoir les notifications électroniquement par email ou dans la Plateforme. Les actions et consentements électroniques sont considérés comme des signatures juridiquement contraignantes.</p>
                        <p><strong>Adresse Email Officielle :</strong> support@speeda.ca</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">17</span>
                        <span>Modifications</span>
                    </h2>
                    <div class="section-content">
                        <p>Speeda peut modifier les présentes Conditions ou mettre à jour la Plateforme à tout moment. L'utilisation continue de la Plateforme après la publication des mises à jour constitue une acceptation des Conditions modifiées.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">18</span>
                        <span>Langues</span>
                    </h2>
                    <div class="section-content">
                        <p>La Plateforme est disponible en anglais, français et arabe. En cas de conflit entre les versions, la version anglaise prévaudra, sauf si la loi en dispose autrement.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">19</span>
                        <span>Dispositions Générales</span>
                    </h2>
                    <div class="section-content">
                        <p>Les présentes Conditions et la Politique de Confidentialité constituent l'intégralité de l'accord entre les Utilisateurs et Speeda. Aucune renonciation à une disposition ne constitue une renonciation continue à une autre. Si une disposition est jugée invalide, elle sera modifiée dans la mesure minimale nécessaire, les autres dispositions restant en vigueur. Les utilisateurs ne peuvent renoncer aux présentes Conditions sans consentement écrit, tandis que Speeda peut les céder à une filiale ou un successeur juridique.</p>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>Informations de Contact</h3>
                    <p><strong>Bnine General Trading Inc. (Speeda)</strong></p>
                    <p>📍 Ottawa, Ontario, Canada</p>
                    <p>Email: <a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                    <p>Site Web: <a href="https://www.Speeda.ca" target="_blank">www.Speeda.ca</a></p>
                </div>

            </div>

        </div>
    </div>

    <script>
        // سكربت بسيط لتبديل اللغات في الملف التجريبي فقط
        function switchLang(lang, btn) {
            // تحديث الاتجاه
            document.documentElement.setAttribute('lang', lang);
            document.documentElement.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');

            // تحديث الأزرار النشطة
            document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // إخفاء/إظهار المحتوى
            document.querySelectorAll('.lang-content').forEach(el => el.classList.remove('active'));
            document.getElementById('content-' + lang).classList.add('active');

            // تحديث العناوين
            const titles = {
                'ar': { main: 'شروط الخدمة', sub: 'اتفاقية منصة Speeda.CA', date: 'آخر تحديث: 23 أكتوبر 2025' },
                'en': { main: 'Terms of Service', sub: 'Speeda.CA Platform Agreement', date: 'Last Updated: October 23, 2025' },
                'fr': { main: "Conditions d'Utilisation", sub: "Contrat de la plateforme Speeda.CA", date: 'Dernière mise à jour : 23 octobre 2025' }
            };

            document.querySelector('.title-text').textContent = titles[lang].main;
            document.getElementById('page-subtitle').textContent = titles[lang].sub;
            document.getElementById('last-updated').textContent = titles[lang].date;
        }

        // تفعيل العربية افتراضياً
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('btn-ar').click();
        });
    </script>
</body>
</html><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/Static/PrivacyPolicy.blade.php ENDPATH**/ ?>