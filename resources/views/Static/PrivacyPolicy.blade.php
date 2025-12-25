<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Speeda</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
        }

        body[dir="rtl"] {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
        }



        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header h1 .icon {
            font-style: normal;
            margin-right: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.95;
        }

        .content {
            padding: 50px 40px;
        }



        .section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section-title {
            color: #667eea;
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        body[dir="rtl"] .section-title {
            flex-direction: row !important;
            justify-content: flex-start !important;
            text-align: right !important;
            display: flex !important;
            align-items: center !important;
        }

        /* ترتيب العناصر بشكل صحيح */
        body[dir="rtl"] .section-title .section-number {
            order: 0 !important;
        }

        body[dir="rtl"] .section-title::after {
            content: '';
            order: 2 !important;
            flex: 1;
        }

        .section-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* 7 حلول مختلفة لإجبار الأرقام على اليمين في RTL */
        body[dir="rtl"] .section-number {
            /* الحل 1: margin من اليمين فقط */
            margin-right: 15px !important;
            margin-left: 0 !important;
            
            /* الحل 2: اتجاه LTR للرقم نفسه */
            direction: ltr !important;
            unicode-bidi: bidi-override !important;
            
            /* الحل 3: order للترتيب */
            order: -1 !important;
            
            /* الحل 4: float لليمين */
            float: right;
            clear: right;
            
            /* الحل 5: position relative مع right */
            position: relative;
            right: auto;
            left: auto;
            
            /* الحل 6: text-align */
            text-align: center !important;
            
            /* الحل 7: transform لإعادة المحاذاة */
            transform: translateX(0) !important;
            
            /* الحل 8: إلغاء أي transform سابق */
            -webkit-transform: none !important;
            -moz-transform: none !important;
        }

        body[dir="rtl"] .section-title > *:not(.section-number) {
            order: 1 !important;
            margin-right: 0 !important;
            margin-left: 0 !important;
            flex: 1;
            text-align: right;
        }

        .section-content {
            color: #555;
            font-size: 1.05em;
        }

        .section-content p {
            margin-bottom: 15px;
        }

        .subsection {
            margin: 20px 0;
            padding-left: 20px;
            border-left: 3px solid #667eea;
        }

        body[dir="rtl"] .subsection {
            padding-left: 0;
            padding-right: 20px;
            border-left: none;
            border-right: 3px solid #667eea;
        }

        .subsection-title {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 10px;
        }

        .definition-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .definition-item {
            margin-bottom: 12px;
        }

        .definition-term {
            font-weight: 600;
            color: #667eea;
        }

        .bullet-list {
            list-style: none;
            padding-left: 0;
        }

        .bullet-list li {
            padding: 8px 0 8px 30px;
            position: relative;
        }

        body[dir="rtl"] .bullet-list li {
            padding: 8px 30px 8px 0;
        }

        .bullet-list li:before {
            content: "•";
            color: #667eea;
            font-weight: bold;
            font-size: 1.3em;
            position: absolute;
            left: 10px;
        }

        body[dir="rtl"] .bullet-list li:before {
            left: auto;
            right: 10px;
        }

        .highlight-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        body[dir="rtl"] .highlight-box {
            border-left: none;
            border-right: 4px solid #ffc107;
        }

        .contact-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-top: 40px;
        }

        .contact-box h3 {
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .contact-box a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 2px solid white;
            transition: opacity 0.3s;
        }

        .contact-box a:hover {
            opacity: 0.8;
        }

        .last-updated {
            text-align: center;
            color: #888;
            font-style: italic;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .header {
                padding: 60px 20px 30px;
            }



            .header h1 {
                font-size: 1.8em;
            }

            .content {
                padding: 30px 20px;
            }

            .section-title {
                font-size: 1.3em;
            }
        }
    </style>
</head>
<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('components.main-nav')
    <div class="container">
        <div class="header">
            <h1>@if(app()->getLocale() === 'en')<span class="icon">🛡</span> Privacy Policy @elseif(app()->getLocale() === 'ar')<span class="icon">🛡</span> سياسة الخصوصية @else <span class="icon">🛡</span> Politique de Confidentialité @endif</h1>
            <p>@if(app()->getLocale() === 'en')Speeda.CA Platform Agreement @elseif(app()->getLocale() === 'ar')اتفاقية منصة Speeda.CA @else Contrat de la plateforme Speeda.CA @endif</p>
        </div>

        <div class="content">
@if(app()->getLocale() === 'en')
                <p class="last-updated">Last Updated: October 14, 2025</p>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        Introduction
                    </h2>
                    <div class="section-content">
                        <p>This Privacy Policy is issued by Bnine General Trading Inc., a legally registered company in Ottawa, Ontario, Canada, acting as the owner and operator of the Speeda platform ("the Platform", "we", "us", or "our").</p>
                        <p>This policy explains how we collect, use, and protect the personal information of users of our website https://speeada.ca ("the Website"), our mobile applications, and related digital services.</p>
                        <p>This policy is an integral part of the Platform's Terms of Use.
                        By using our site, you confirm that you have read, understood, and agreed to this policy.
                        If you do not agree with any part of it, please immediately cease using the Platform.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        Legal Definitions
                    </h2>
                    <div class="section-content">
                        <p>For the purposes of this policy, the following definitions apply:</p>
                        <div class="definition-list">
                            <div class="definition-item">
                                <span class="definition-term">Bnine General Trading Inc.:</span> The legal entity registered in Canada responsible for operating and managing the Platform.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Speeda / Speeda.ca:</span> The trade name and digital platform owned and managed by Bnine General Trading Inc.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">User:</span> Any person who uses the Website or application, whether as a Client or a Service Provider.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Client:</span> A User who searches for a service or submits a service request via the Platform.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Service Provider:</span> A User who offers services via the Platform, as an independent individual or a business entity.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Personal Information:</span> Any data that can directly or indirectly identify a specific individual, as defined by the Personal Information Protection and Electronic Documents Act (PIPEDA) in Canada.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        Scope of Application
                    </h2>
                    <div class="section-content">
                        <p>This policy applies to all users inside and outside of Canada who access or use the Platform.
                        By using our services, you consent to your personal data being collected, processed, and stored in Canada or in other countries that have a similar level of data protection.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">4</span>
                        Nature of the Platform
                    </h2>
                    <div class="section-content">
                        <p>Speeda operates as an intermediary digital platform that connects Clients and Service Providers in a secure and reliable environment.
                        Speeda does not provide any services directly, nor is it a party to any contract or agreement between Clients and Service Providers.
                        All dealings and transactions between users are conducted at their own risk.
                        Our role is limited to providing the technical infrastructure that facilitates communication between parties.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        Information We Collect
                    </h2>
                    <div class="section-content">
                        <p>We may collect multiple types of information depending on the nature of your use of the Platform:</p>

                        <div class="subsection">
                            <p class="subsection-title">A. Information Provided Directly by You:</p>
                            <ul class="bullet-list">
                                <li>Full name, email address, and phone number.</li>
                                <li>City or province, and the type of service required or offered.</li>
                                <li>Payment or billing data (for subscriptions or transactions).</li>
                                <li>Professional or personal identification documents (such as a business license or commercial registration number).</li>
                                <li>Any correspondence or interactions with the support team or other users via the Platform.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">B. Technical and Analytical Data:</p>
                            <ul class="bullet-list">
                                <li>Your device's IP address.</li>
                                <li>Browser type, operating system, and device information.</li>
                                <li>Approximate location data (city or region).</li>
                                <li>Access times, pages visited, and browsing patterns.</li>
                                <li>Cookies and analytics data.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">C. Activity Data:</p>
                            <ul class="bullet-list">
                                <li>History of completed services or requests.</li>
                                <li>Ratings, reviews, and comments exchanged between users.</li>
                                <li>Transaction history and notifications.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">D. Information Received from Third Parties:</p>
                            <p>We may receive additional data from our trusted partners such as:</p>
                            <ul class="bullet-list">
                                <li>Identity verification services.</li>
                                <li>Electronic payment service providers.</li>
                                <li>Social media platforms (when registering through them and with your consent).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        Purposes of Data Collection
                    </h2>
                    <div class="section-content">
                        <p>We use personal information for the following purposes:</p>
                        <ul class="bullet-list">
                            <li>Operating and maintaining the Platform and digital services.</li>
                            <li>Facilitating communication between Clients and Service Providers.</li>
                            <li>Verifying identity and managing user accounts.</li>
                            <li>Ensuring the security of the Platform and the integrity of transactions.</li>
                            <li>Improving performance and user experience.</li>
                            <li>Communicating with you regarding technical support, notifications, or updates.</li>
                            <li>Complying with legal and regulatory obligations.</li>
                            <li>Processing complaints and resolving disputes.</li>
                            <li>Conducting statistical analysis and research.</li>
                            <li>Sending promotional offers or marketing messages (with your prior consent).</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        Data Sharing and Disclosure
                    </h2>
                    <div class="section-content">
                        <p>We may share your information only in the following cases:</p>

                        <div class="subsection">
                            <p class="subsection-title">A) With Technical Service Providers:</p>
                            <ul class="bullet-list">
                                <li>Cloud data hosting services.</li>
                                <li>Payment processing systems.</li>
                                <li>Technical support and analytics partners.</li>
                            </ul>
                            <p>All these parties are bound by strict confidentiality and data protection agreements.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">B) Legal Obligations:</p>
                            <p>We may disclose certain information to comply with court orders or requests from government authorities in accordance with the law.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">C) Mergers or Acquisitions:</p>
                            <p>In the event of a merger, acquisition, or sale of part of our assets, user data may be transferred after prior notification.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">D) Fraud Prevention and Security Risks:</p>
                            <p>When we believe in good faith that disclosure is necessary to protect our rights, users, or the public from fraudulent or illegal activity.</p>
                        </div>

                        <div class="highlight-box">
                            <strong>Note:</strong> We do not sell, rent, or trade personal information under any circumstances.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        Data Protection and Information Security
                    </h2>
                    <div class="section-content">
                        <p>Bnine General Trading Inc. follows advanced security standards, including:</p>
                        <ul class="bullet-list">
                            <li>SSL protocol encryption for communications.</li>
                            <li>Secure storage on limited-access servers.</li>
                            <li>Periodic reviews and security vulnerability assessments.</li>
                            <li>Strict internal policies for data access control.</li>
                        </ul>
                        <p>Despite our commitment to the highest security standards, no digital system is 100% secure.
                        Users are responsible for maintaining the confidentiality of their login credentials.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        Data Retention
                    </h2>
                    <div class="section-content">
                        <p>We retain your personal information only for the period necessary to fulfill the purposes mentioned in this policy or to comply with laws (such as tax or accounting).
                        After the retention period ends, the data is deleted or anonymized.</p>
                        <p>Users can request the deletion of their data at any time via email:
                        <a href="mailto:support@Speeda.ca">support@Speeda.ca</a></p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        User Rights
                    </h2>
                    <div class="section-content">
                        <p>According to PIPEDA, users have the right to:</p>
                        <ul class="bullet-list">
                            <li>Access their personal data that we hold.</li>
                            <li>Request correction of any inaccurate or incomplete data.</li>
                            <li>Withdraw their consent to data processing at any time.</li>
                            <li>Request the deletion of their personal data.</li>
                            <li>Object to any unnecessary use of their data.</li>
                        </ul>
                        <p>Please direct requests to the following email:
                        <a href="mailto:info@Speeda.ca">info@Speeda.ca</a></p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        Children's Privacy
                    </h2>
                    <div class="section-content">
                        <p>Our services are directed to adults (18 years and older).
                        We do not knowingly collect personal data from minors without parental or guardian consent.
                        If such data is discovered, it will be permanently deleted.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        Limitation of Liability
                    </h2>
                    <div class="section-content">
                        <p>Since Speeda operates only as an intermediary platform:</p>
                        <ul class="bullet-list">
                            <li>We are not responsible for any misconduct, negligence, or damages caused by any user or Service Provider.</li>
                            <li>We do not guarantee the accuracy, reliability, or quality of services provided by Service Providers.</li>
                            <li>We are not a party to any contract or agreement concluded between Clients and Service Providers.</li>
                            <li>Any disputes or claims between users are resolved directly without any liability on Bnine General Trading Inc.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        Cookies
                    </h2>
                    <div class="section-content">
                        <p>Our site uses cookies in order to:</p>
                        <ul class="bullet-list">
                            <li>Improve the user experience.</li>
                            <li>Analyze traffic and user behavior.</li>
                            <li>Save language and personal preferences.</li>
                        </ul>
                        <p>You can disable cookies from your browser settings, knowing that some features of the site may not function fully as a result.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        International Data Transfer
                    </h2>
                    <div class="section-content">
                        <p>Your data may be stored or processed on secure servers located in Canada or the United States of America.
                        We adhere to all international data protection standards, including the rules governing data transfer between the European Union and Canada.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        Privacy Policy Updates
                    </h2>
                    <div class="section-content">
                        <p>Bnine General Trading Inc. reserves the right to modify or update this policy at any time.
                        Any changes will be posted on this page with the last updated date.
                        Your continued use of the Platform after any modification constitutes an implicit agreement to the updated policy.</p>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>Contact Information</h3>
                    <p><strong>Bnine General Trading Inc.</strong></p>
                    <p>Owner and Operator of the Speeda Platform</p>
                    <p>📍 Headquarters: Ottawa, Ontario, Canada</p>
                    <p>Email: <a href="mailto:info@Speeda.ca">info@Speeda.ca</a> / <a href="mailto:support@Speeda.ca">support@Speeda.ca</a></p>
                    <p>Website: <a href="https://speeada.ca" target="_blank">https://speeada.ca</a></p>
                </div>

@elseif(app()->getLocale() === 'ar')
                <p class="last-updated">تاريخ آخر تحديث: 14 أكتوبر 2025</p>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        المقدّمة
                    </h2>
                    <div class="section-content">
                        <p>تُصدر هذه سياسة الخصوصية من قبل شركة Bnine General Trading Inc.، وهي شركة مسجلة قانونيًا في مدينة أوتاوا، أونتاريو، كندا، وتعمل بصفتها المالك والمشغّل لمنصة Speeda ("المنصة"، "نحن"، "لنا"، أو "خاصتنا").</p>
                        <p>توضح هذه السياسة كيفية جمعنا واستخدامنا وحمايتنا للمعلومات الشخصية الخاصة بمستخدمي موقعنا الإلكتروني https://speeada.ca ("الموقع")، وتطبيقاتنا المحمولة، والخدمات الرقمية ذات الصلة.</p>
                        <p>تُعد هذه السياسة جزءًا لا يتجزأ من شروط استخدام المنصة.
                        باستخدامك لموقعنا، فإنك تؤكد أنك قد قرأت هذه السياسة وفهمتها ووافقت عليها.
                        إذا لم توافق على أي جزء منها، يُرجى التوقف فورًا عن استخدام المنصة.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        التعريفات القانونية
                    </h2>
                    <div class="section-content">
                        <p>لأغراض هذه السياسة، تُعتمد التعريفات التالية:</p>
                        <div class="definition-list">
                            <div class="definition-item">
                                <span class="definition-term">شركة Bnine General Trading Inc.:</span> الكيان القانوني المسجّل في كندا والمسؤول عن تشغيل وإدارة المنصة.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Speeda / Speeda.ca:</span> الاسم التجاري والمنصة الرقمية المملوكة والمدارة من قبل شركة Bnine General Trading Inc.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">المستخدم:</span> أي شخص يستخدم الموقع أو التطبيق، سواء بصفته عميلًا أو مزوّد خدمة.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">العميل:</span> المستخدم الذي يبحث عن خدمة أو يقدّم طلب خدمة عبر المنصة.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">مزوّد الخدمة:</span> المستخدم الذي يقدّم خدمات عبر المنصة، بصفته فردًا مستقلاً أو كيانًا تجاريًا.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">المعلومات الشخصية:</span> أي بيانات يمكنها التعريف بشكل مباشر أو غير مباشر بفرد معيّن، وفقًا لتعريف قانون حماية المعلومات الشخصية والوثائق الإلكترونية (PIPEDA) في كندا.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        نطاق التطبيق
                    </h2>
                    <div class="section-content">
                        <p>تنطبق هذه السياسة على جميع المستخدمين داخل كندا وخارجها الذين يصلون إلى المنصة أو يستخدمونها.
                        وباستخدام خدماتنا، فإنك توافق على أن يتم جمع ومعالجة وتخزين بياناتك الشخصية في كندا أو في دول أخرى تتمتع بمستوى مماثل من حماية البيانات.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">4</span>
                        طبيعة المنصة
                    </h2>
                    <div class="section-content">
                        <p>تعمل Speeda كمنصة رقمية وسيطة تربط بين العملاء ومزوّدي الخدمات في بيئة آمنة وموثوقة.
                        لا تُقدّم Speeda أي خدمات مباشرة، كما أنها ليست طرفًا في أي عقد أو اتفاق بين العملاء ومزوّدي الخدمات.
                        تُجرى جميع التعاملات والمعاملات بين المستخدمين على مسؤوليتهم الخاصة.
                        ويقتصر دورنا على توفير البنية التقنية التي تُسهّل التواصل بين الأطراف.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        المعلومات التي نجمعها
                    </h2>
                    <div class="section-content">
                        <p>قد نقوم بجمع أنواع متعددة من المعلومات تبعًا لطبيعة استخدامك للمنصة:</p>

                        <div class="subsection">
                            <p class="subsection-title">أ. المعلومات المقدّمة مباشرة من قبلك:</p>
                            <ul class="bullet-list">
                                <li>الاسم الكامل، البريد الإلكتروني، ورقم الهاتف.</li>
                                <li>المدينة أو المقاطعة، ونوع الخدمة المطلوبة أو المقدّمة.</li>
                                <li>بيانات الدفع أو الفواتير (للاشتراكات أو المعاملات).</li>
                                <li>وثائق التعريف المهني أو الشخصي (مثل رخصة العمل أو رقم التسجيل التجاري).</li>
                                <li>أي مراسلات أو تفاعلات مع فريق الدعم أو المستخدمين الآخرين عبر المنصة.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">ب. البيانات التقنية والتحليلية:</p>
                            <ul class="bullet-list">
                                <li>عنوان IP الخاص بجهازك.</li>
                                <li>نوع المتصفح ونظام التشغيل ومعلومات الجهاز.</li>
                                <li>بيانات الموقع التقريبية (المدينة أو المنطقة).</li>
                                <li>أوقات الوصول، الصفحات التي تمت زيارتها، وأنماط التصفح.</li>
                                <li>بيانات ملفات تعريف الارتباط (Cookies) والتحليلات.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">ج. بيانات النشاط:</p>
                            <ul class="bullet-list">
                                <li>سجل الخدمات أو الطلبات المكتملة.</li>
                                <li>التقييمات والمراجعات والتعليقات المتبادلة بين المستخدمين.</li>
                                <li>سجل المعاملات والإشعارات.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">د. المعلومات المستلمة من أطراف ثالثة:</p>
                            <p>قد نتلقى بيانات إضافية من شركائنا الموثوقين مثل:</p>
                            <ul class="bullet-list">
                                <li>خدمات التحقق من الهوية.</li>
                                <li>مزوّدي خدمات الدفع الإلكتروني.</li>
                                <li>منصات التواصل الاجتماعي (عند التسجيل من خلالها وبموافقتك).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        أهداف جمع البيانات
                    </h2>
                    <div class="section-content">
                        <p>نستخدم المعلومات الشخصية للأغراض التالية:</p>
                        <ul class="bullet-list">
                            <li>تشغيل وصيانة المنصة والخدمات الرقمية.</li>
                            <li>تسهيل التواصل بين العملاء ومزوّدي الخدمات.</li>
                            <li>التحقق من الهوية وإدارة حسابات المستخدمين.</li>
                            <li>ضمان سلامة المنصة ونزاهة التعاملات.</li>
                            <li>تحسين الأداء وتجربة المستخدم.</li>
                            <li>التواصل معك بشأن الدعم الفني أو الإشعارات أو التحديثات.</li>
                            <li>الامتثال للالتزامات القانونية والتنظيمية.</li>
                            <li>معالجة الشكاوى وحل النزاعات.</li>
                            <li>إجراء التحليلات والأبحاث الإحصائية.</li>
                            <li>إرسال العروض الترويجية أو الرسائل التسويقية (بموافقتك المسبقة).</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        مشاركة البيانات والإفصاح عنها
                    </h2>
                    <div class="section-content">
                        <p>يجوز لنا مشاركة معلوماتك فقط في الحالات التالية:</p>

                        <div class="subsection">
                            <p class="subsection-title">أ) مع مزوّدي الخدمات التقنية:</p>
                            <ul class="bullet-list">
                                <li>خدمات استضافة البيانات السحابية.</li>
                                <li>أنظمة معالجة المدفوعات.</li>
                                <li>شركاء الدعم الفني والتحليلات.</li>
                            </ul>
                            <p>تلتزم جميع هذه الجهات باتفاقيات صارمة للسرية وحماية البيانات.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">ب) الالتزامات القانونية:</p>
                            <p>قد نُفصح عن معلومات معينة للامتثال لأوامر قضائية أو طلبات من السلطات الحكومية وفقًا للقانون.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">ج) عمليات الدمج أو الاستحواذ:</p>
                            <p>في حال حدوث دمج أو استحواذ أو بيع جزء من أصولنا، قد يتم نقل بيانات المستخدمين بعد إشعارهم المسبق.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">د) مكافحة الاحتيال والمخاطر الأمنية:</p>
                            <p>عندما نعتقد بحسن نية أن الإفصاح ضروري لحماية حقوقنا أو المستخدمين أو الجمهور من نشاط احتيالي أو غير قانوني.</p>
                        </div>

                        <div class="highlight-box">
                            <strong>ملاحظة:</strong> لا نقوم ببيع أو تأجير أو المتاجرة بالمعلومات الشخصية تحت أي ظرف.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        حماية البيانات وأمن المعلومات
                    </h2>
                    <div class="section-content">
                        <p>تتّبع شركة Bnine General Trading Inc. معايير أمان متقدمة تشمل:</p>
                        <ul class="bullet-list">
                            <li>تشفير الاتصالات عبر بروتوكول SSL.</li>
                            <li>تخزين آمن على خوادم محدودة الوصول.</li>
                            <li>مراجعات دورية وتقييمات للثغرات الأمنية.</li>
                            <li>سياسات داخلية صارمة للتحكم في الوصول إلى البيانات.</li>
                        </ul>
                        <p>ورغم التزامنا بأعلى معايير الأمان، لا يوجد نظام رقمي آمن بنسبة 100%.
                        ويتحمل المستخدمون مسؤولية الحفاظ على سرّية بيانات الدخول الخاصة بهم.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        الاحتفاظ بالبيانات
                    </h2>
                    <div class="section-content">
                        <p>نحتفظ بمعلوماتك الشخصية فقط للمدة اللازمة لتحقيق الأغراض المذكورة في هذه السياسة أو للامتثال للقوانين (مثل الضرائب أو المحاسبة).
                        وبعد انتهاء فترة الاحتفاظ، تُحذف البيانات أو تُحوّل إلى صيغة مجهولة الهوية.</p>
                        <p>يمكن للمستخدمين طلب حذف بياناتهم في أي وقت عبر البريد الإلكتروني:
                        <a href="mailto:support@Speeda.ca">support@Speeda.ca</a></p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        حقوق المستخدم
                    </h2>
                    <div class="section-content">
                        <p>وفقًا لقانون PIPEDA، يحق للمستخدمين ما يلي:</p>
                        <ul class="bullet-list">
                            <li>الوصول إلى بياناتهم الشخصية التي نحتفظ بها.</li>
                            <li>طلب تصحيح أي بيانات غير دقيقة أو غير مكتملة.</li>
                            <li>سحب موافقتهم على معالجة البيانات في أي وقت.</li>
                            <li>طلب حذف بياناتهم الشخصية.</li>
                            <li>الاعتراض على أي استخدام غير ضروري لبياناتهم.</li>
                        </ul>
                        <p>يُرجى توجيه الطلبات إلى البريد التالي:
                        <a href="mailto:info@Speeda.ca">info@Speeda.ca</a></p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        خصوصية الأطفال
                    </h2>
                    <div class="section-content">
                        <p>خدماتنا موجهة للبالغين (18 سنة فما فوق).
                        ولا نقوم بجمع بيانات شخصية من القاصرين دون موافقة الوالدين أو الأوصياء.
                        وفي حال اكتشاف بيانات من هذا النوع، سيتم حذفها نهائيًا.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        تحديد المسؤولية
                    </h2>
                    <div class="section-content">
                        <p>نظرًا لأن Speeda تعمل فقط كمنصة وسيطة:</p>
                        <ul class="bullet-list">
                            <li>فإننا غير مسؤولين عن أي سوء تصرّف أو إهمال أو أضرار ناتجة عن أي مستخدم أو مزوّد خدمة.</li>
                            <li>لا نضمن دقة أو موثوقية أو جودة الخدمات التي يقدّمها مزوّدو الخدمات.</li>
                            <li>لسنا طرفًا في أي عقد أو اتفاق مبرم بين العملاء ومزوّدي الخدمات.</li>
                            <li>تُحلّ أي نزاعات أو مطالبات بين المستخدمين مباشرة دون أي مسؤولية على شركة Bnine General Trading Inc..</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        ملفات تعريف الارتباط (Cookies)
                    </h2>
                    <div class="section-content">
                        <p>يستخدم موقعنا ملفات تعريف الارتباط من أجل:</p>
                        <ul class="bullet-list">
                            <li>تحسين تجربة المستخدم.</li>
                            <li>تحليل حركة الزيارات وسلوك المستخدمين.</li>
                            <li>حفظ اللغة والتفضيلات الشخصية.</li>
                        </ul>
                        <p>يمكنك تعطيل ملفات تعريف الارتباط من إعدادات المتصفح، مع العلم أن بعض ميزات الموقع قد لا تعمل بشكل كامل نتيجة لذلك.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        نقل البيانات الدولي
                    </h2>
                    <div class="section-content">
                        <p>قد تُخزّن بياناتك أو تُعالَج على خوادم آمنة تقع في كندا أو الولايات المتحدة الأمريكية.
                        ونحن نلتزم بجميع معايير حماية البيانات الدولية، بما في ذلك القواعد المنظمة لنقل البيانات بين الاتحاد الأوروبي وكندا.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        تحديثات سياسة الخصوصية
                    </h2>
                    <div class="section-content">
                        <p>تحتفظ شركة Bnine General Trading Inc. بالحق في تعديل أو تحديث هذه السياسة في أي وقت.
                        سيتم نشر أي تغييرات على هذه الصفحة مع ذكر تاريخ آخر تحديث.
                        يُعد استمرارك في استخدام المنصة بعد أي تعديل موافقة ضمنية على السياسة المُحدّثة.</p>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>معلومات الاتصال</h3>
                    <p><strong>Bnine General Trading Inc.</strong></p>
                    <p>المالك والمشغّل لمنصة Speeda</p>
                    <p>📍 المقر الرئيسي: أوتاوا، أونتاريو، كندا</p>
                    <p>البريد الإلكتروني: <a href="mailto:info@Speeda.ca">info@Speeda.ca</a> / <a href="mailto:support@Speeda.ca">support@Speeda.ca</a></p>
                    <p>الموقع الإلكتروني: <a href="https://speeada.ca" target="_blank">https://speeada.ca</a></p>
                </div>

@else
                <p class="last-updated">Dernière mise à jour : 14 octobre 2025</p>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        Introduction
                    </h2>
                    <div class="section-content">
                        <p>Cette Politique de Confidentialité est émise par Bnine General Trading Inc., une société légalement enregistrée à Ottawa, Ontario, Canada, agissant en tant que propriétaire et exploitant de la plateforme Speeda (« la Plateforme », « nous », « notre » ou « nos »).</p>
                        <p>Cette politique explique comment nous collectons, utilisons et protégeons les informations personnelles des utilisateurs de notre site Web https://speeada.ca (« le Site Web »), de nos applications mobiles et des services numériques associés.</p>
                        <p>Cette politique fait partie intégrante des Conditions d'utilisation de la Plateforme.
                        En utilisant notre site, vous confirmez avoir lu, compris et accepté cette politique.
                        Si vous n'êtes pas d'accord avec une partie de celle-ci, veuillez cesser immédiatement d'utiliser la Plateforme.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        Définitions Légales
                    </h2>
                    <div class="section-content">
                        <p>Aux fins de cette politique, les définitions suivantes s'appliquent :</p>
                        <div class="definition-list">
                            <div class="definition-item">
                                <span class="definition-term">Bnine General Trading Inc. :</span> L'entité juridique enregistrée au Canada, responsable de l'exploitation et de la gestion de la Plateforme.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Speeda / Speeda.ca :</span> Le nom commercial et la plateforme numérique détenus et gérés par Bnine General Trading Inc.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Utilisateur :</span> Toute personne qui utilise le Site Web ou l'application, que ce soit en tant que Client ou Prestataire de services.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Client :</span> Un Utilisateur qui recherche un service ou soumet une demande de service via la Plateforme.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Prestataire de services :</span> Un Utilisateur qui propose des services via la Plateforme, en tant qu'individu indépendant ou entité commerciale.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Informations Personnelles :</span> Toute donnée pouvant identifier directement ou indirectement un individu spécifique, telle que définie par la Loi sur la protection des renseignements personnels et les documents électroniques (LPRPDE) au Canada.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        Champ d'Application
                    </h2>
                    <div class="section-content">
                        <p>Cette politique s'applique à tous les utilisateurs, à l'intérieur et à l'extérieur du Canada, qui accèdent ou utilisent la Plateforme.
                        En utilisant nos services, vous consentez à ce que vos données personnelles soient collectées, traitées et stockées au Canada ou dans d'autres pays bénéficiant d'un niveau similaire de protection des données.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">4</span>
                        Nature de la Plateforme
                    </h2>
                    <div class="section-content">
                        <p>Speeda fonctionne comme une plateforme numérique intermédiaire qui met en relation les Clients et les Prestataires de services dans un environnement sécurisé et fiable.
                        Speeda ne fournit aucun service directement et n'est partie à aucun contrat ou accord entre les Clients et les Prestataires de services.
                        Toutes les interactions et transactions entre les utilisateurs sont effectuées à leurs propres risques.
                        Notre rôle se limite à fournir l'infrastructure technique qui facilite la communication entre les parties.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        Informations que nous collectons
                    </h2>
                    <div class="section-content">
                        <p>Nous pouvons collecter plusieurs types d'informations en fonction de la nature de votre utilisation de la Plateforme :</p>

                        <div class="subsection">
                            <p class="subsection-title">A. Informations fournies directement par vous :</p>
                            <ul class="bullet-list">
                                <li>Nom complet, adresse e-mail et numéro de téléphone.</li>
                                <li>Ville ou province, et le type de service requis ou offert.</li>
                                <li>Données de paiement ou de facturation (pour les abonnements ou transactions).</li>
                                <li>Documents d'identification professionnelle ou personnelle (tels qu'une licence commerciale ou un numéro d'enregistrement commercial).</li>
                                <li>Toute correspondance ou interaction avec l'équipe de support ou d'autres utilisateurs via la Plateforme.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">B. Données techniques et analytiques :</p>
                            <ul class="bullet-list">
                                <li>L'adresse IP de votre appareil.</li>
                                <li>Type de navigateur, système d'exploitation et informations sur l'appareil.</li>
                                <li>Données de localisation approximative (ville ou région).</li>
                                <li>Heures d'accès, pages visitées et habitudes de navigation.</li>
                                <li>Données de témoins (Cookies) et d'analyse.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">C. Données d'activité :</p>
                            <ul class="bullet-list">
                                <li>Historique des services complétés ou des demandes.</li>
                                <li>Évaluations, avis et commentaires échangés entre utilisateurs.</li>
                                <li>Historique des transactions et notifications.</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">D. Informations reçues de tiers :</p>
                            <p>Nous pouvons recevoir des données supplémentaires de nos partenaires de confiance tels que :</p>
                            <ul class="bullet-list">
                                <li>Services de vérification d'identité.</li>
                                <li>Fournisseurs de services de paiement électronique.</li>
                                <li>Plateformes de médias sociaux (lors de l'inscription via celles-ci et avec votre consentement).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        Objectifs de la collecte de données
                    </h2>
                    <div class="section-content">
                        <p>Nous utilisons les informations personnelles aux fins suivantes :</p>
                        <ul class="bullet-list">
                            <li>Exploiter et maintenir la Plateforme et les services numériques.</li>
                            <li>Faciliter la communication entre les Clients et les Prestataires de services.</li>
                            <li>Vérifier l'identité et gérer les comptes utilisateurs.</li>
                            <li>Assurer la sécurité de la Plateforme et l'intégrité des transactions.</li>
                            <li>Améliorer la performance et l'expérience utilisateur.</li>
                            <li>Communiquer avec vous concernant le support technique, les notifications ou les mises à jour.</li>
                            <li>Se conformer aux obligations légales et réglementaires.</li>
                            <li>Traiter les réclamations et résoudre les litiges.</li>
                            <li>Effectuer des analyses statistiques et des recherches.</li>
                            <li>Envoyer des offres promotionnelles ou des messages marketing (avec votre consentement préalable).</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        Partage et divulgation des données
                    </h2>
                    <div class="section-content">
                        <p>Nous ne pouvons partager vos informations que dans les cas suivants :</p>

                        <div class="subsection">
                            <p class="subsection-title">A) Avec les fournisseurs de services techniques :</p>
                            <ul class="bullet-list">
                                <li>Services d'hébergement de données en nuage.</li>
                                <li>Systèmes de traitement des paiements.</li>
                                <li>Partenaires de support technique et d'analyse.</li>
                            </ul>
                            <p>Toutes ces parties sont liées par des accords stricts de confidentialité et de protection des données.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">B) Obligations légales :</p>
                            <p>Nous pouvons divulguer certaines informations pour nous conformer à des ordonnances judiciaires ou à des demandes des autorités gouvernementales conformément à la loi.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">C) Fusions ou acquisitions :</p>
                            <p>En cas de fusion, d'acquisition ou de vente d'une partie de nos actifs, les données des utilisateurs peuvent être transférées après notification préalable.</p>
                        </div>

                        <div class="subsection">
                            <p class="subsection-title">D) Prévention de la fraude et risques de sécurité :</p>
                            <p>Lorsque nous estimons de bonne foi que la divulgation est nécessaire pour protéger nos droits, ceux des utilisateurs ou le public contre une activité frauduleuse ou illégale.</p>
                        </div>

                        <div class="highlight-box">
                            <strong>Remarque :</strong> Nous ne vendons, ne louons et n'échangeons en aucun cas les informations personnelles.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        Protection des données et sécurité de l'information
                    </h2>
                    <div class="section-content">
                        <p>Bnine General Trading Inc. respecte des normes de sécurité avancées, notamment :</p>
                        <ul class="bullet-list">
                            <li>Cryptage des communications par protocole SSL.</li>
                            <li>Stockage sécurisé sur des serveurs à accès limité.</li>
                            <li>Examens périodiques et évaluations des vulnérabilités de sécurité.</li>
                            <li>Politiques internes strictes pour le contrôle d'accès aux données.</li>
                        </ul>
                        <p>Malgré notre engagement envers les normes de sécurité les plus élevées, aucun système numérique n'est sécurisé à 100 %.
                        Les utilisateurs sont responsables du maintien de la confidentialité de leurs identifiants de connexion.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        Conservation des données
                    </h2>
                    <div class="section-content">
                        <p>Nous ne conservons vos informations personnelles que pour la durée nécessaire à la réalisation des objectifs mentionnés dans cette politique ou pour nous conformer aux lois (telles que fiscales ou comptables).
                        À l'expiration de la période de conservation, les données sont supprimées ou anonymisées.</p>
                        <p>Les utilisateurs peuvent demander la suppression de leurs données à tout moment par e-mail :
                        <a href="mailto:support@Speeda.ca">support@Speeda.ca</a></p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        Droits des utilisateurs
                    </h2>
                    <div class="section-content">
                        <p>Conformément à la LPRPDE, les utilisateurs ont le droit de :</p>
                        <ul class="bullet-list">
                            <li>Accéder à leurs données personnelles que nous détenons.</li>
                            <li>Demander la correction de toute donnée inexacte ou incomplète.</li>
                            <li>Retirer leur consentement au traitement des données à tout moment.</li>
                            <li>Demander la suppression de leurs données personnelles.</li>
                            <li>S'opposer à toute utilisation non nécessaire de leurs données.</li>
                        </ul>
                        <p>Veuillez adresser vos demandes à l'adresse e-mail suivante :
                        <a href="mailto:info@Speeda.ca">info@Speeda.ca</a></p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        Confidentialité des enfants
                    </h2>
                    <div class="section-content">
                        <p>Nos services sont destinés aux adultes (18 ans et plus).
                        Nous ne collectons pas sciemment de données personnelles auprès de mineurs sans le consentement des parents ou tuteurs.
                        Si de telles données sont découvertes, elles seront définitivement supprimées.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        Limitation de responsabilité
                    </h2>
                    <div class="section-content">
                        <p>Étant donné que Speeda n'agit qu'en tant que plateforme intermédiaire :</p>
                        <ul class="bullet-list">
                            <li>Nous ne sommes pas responsables de toute faute, négligence ou dommage causé par un utilisateur ou un Prestataire de services.</li>
                            <li>Nous ne garantissons pas l'exactitude, la fiabilité ou la qualité des services fournis par les Prestataires de services.</li>
                            <li>Nous ne sommes partie à aucun contrat ou accord conclu entre les Clients et les Prestataires de services.</li>
                            <li>Tout litige ou réclamation entre utilisateurs est résolu directement sans aucune responsabilité de Bnine General Trading Inc.</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        Témoins (Cookies)
                    </h2>
                    <div class="section-content">
                        <p>Notre site utilise des témoins afin de :</p>
                        <ul class="bullet-list">
                            <li>Améliorer l'expérience utilisateur.</li>
                            <li>Analyser le trafic et le comportement des utilisateurs.</li>
                            <li>Enregistrer la langue et les préférences personnelles.</li>
                        </ul>
                        <p>Vous pouvez désactiver les témoins dans les paramètres de votre navigateur, sachant que certaines fonctionnalités du site pourraient ne pas fonctionner pleinement en conséquence.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        Transfert international de données
                    </h2>
                    <div class="section-content">
                        <p>Vos données peuvent être stockées ou traitées sur des serveurs sécurisés situés au Canada ou aux États-Unis d'Amérique.
                        Nous adhérons à toutes les normes internationales de protection des données, y compris les règles régissant le transfert de données entre l'Union européenne et le Canada.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        Mises à jour de la politique de confidentialité
                    </h2>
                    <div class="section-content">
                        <p>Bnine General Trading Inc. se réserve le droit de modifier ou de mettre à jour cette politique à tout moment.
                        Toute modification sera publiée sur cette page avec la date de la dernière mise à jour.
                        Votre utilisation continue de la Plateforme après toute modification constitue un accord implicite avec la politique mise à jour.</p>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>Coordonnées</h3>
                    <p><strong>Bnine General Trading Inc.</strong></p>
                    <p>Propriétaire et exploitant de la plateforme Speeda</p>
                    <p>📍 Siège social : Ottawa, Ontario, Canada</p>
                    <p>Email: <a href="mailto:info@Speeda.ca">info@Speeda.ca</a> / <a href="mailto:support@Speeda.ca">support@Speeda.ca</a></p>
                    <p>Site Web: <a href="https://speeada.ca" target="_blank">https://speeada.ca</a></p>
                </div>

@endif
        </div>
    </div>
    </body>
</html>
