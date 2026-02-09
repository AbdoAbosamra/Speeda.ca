<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Speeda</title>
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
            <h1>@if(app()->getLocale() === 'en')Terms of Service @elseif(app()->getLocale() === 'ar')شروط الخدمة @else Conditions d'utilisation @endif</h1>
            <p>@if(app()->getLocale() === 'en')Speeda.CA Platform Agreement @elseif(app()->getLocale() === 'ar')اتفاقية منصة Speeda.CA @else Contrat de la plateforme Speeda.CA @endif</p>
        </div>

        <div class="content">
@if(app()->getLocale() === 'en')
            <p class="last-updated">Last Updated: October 23, 2025</p>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        Definitions and Parties
                    </h2>
                    <div class="section-content">
                        <p>These Terms of Service ("Terms") constitute a legally binding agreement between you and Bnine General Trading Inc., the owner and operator of the Speeda platform ("Speeda", "we", "us", or "our"), headquartered in Ottawa, Ontario, Canada. These Terms govern your use of www.Speeda.ca and any related digital services (collectively referred to as the "Platform").</p>

                        <div class="definition-list">
                            <div class="definition-item">
                                <span class="definition-term">Client:</span> Any individual or business seeking a service provider through the Platform.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Service Provider:</span> Any individual or business offering services independently through the Platform.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">User:</span> Includes both Clients and Service Providers.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Service Agreement:</span> The direct contract or understanding between a Client and Service Provider regarding a specific service.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Payment Service Provider (PSP):</span> Any third-party payment processor (such as PayPal, e-Transfer, or others).
                            </div>
                        </div>

                        <p>By accessing or using the Platform, you agree to these Terms and our Privacy Policy. If you do not agree, you must immediately cease using the Platform.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        Eligibility and Account Creation
                    </h2>
                    <div class="section-content">
                        <p>Users must be at least 18 years old and legally qualified to enter into contracts. Users are required to provide accurate and current information and are responsible for maintaining the confidentiality of login credentials and all account activities. Speeda reserves the right to suspend, refuse, or terminate any account to protect users or comply with the law.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        Nature of Speeda's Role
                    </h2>
                    <div class="section-content">
                        <div class="highlight-box">
                            <strong>Important:</strong> Speeda operates solely as a technology intermediary connecting Clients and Service Providers. Speeda does not provide services itself, does not employ Service Providers, does not set prices, and does not supervise the quality, timing, or outcomes of services.
                        </div>
                        <p>No provision in these Terms creates an employment, agency, or partnership relationship between Speeda and any User.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">4</span>
                        Platform Use and Service Agreements
                    </h2>
                    <div class="section-content">
                        <p>Clients freely choose Service Providers based on information available on the Platform. Once a Client and Service Provider reach an agreement, the Service Agreement is formed exclusively between them. Speeda is not a party to any Service Agreement and bears no legal or financial responsibility for its performance, outcomes, or any disputes arising from it.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        Subscriptions, Fees, Payments, and Cancellation
                    </h2>
                    <div class="section-content">
                        <p>Speeda operates on a monthly subscription system that grants Users access to the Platform's features and services.</p>

                        <ul class="bullet-list">
                            <li>All fees are entirely non-refundable, in whole or in part</li>
                            <li>Users may cancel their subscription at any time, with cancellation taking effect from the beginning of the next billing cycle</li>
                            <li>No refunds are provided for unused periods</li>
                            <li>Payments can be made via e-Transfer, PayPal, or any other payment provider supported by Speeda</li>
                            <li>By using any payment provider, Users agree to its terms and acknowledge that Speeda is not a bank or escrow agent</li>
                            <li>Applicable taxes (such as HST/GST/PST) may be added as required by law</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        Service Provider Obligations
                    </h2>
                    <div class="section-content">
                        <p>Service Providers acknowledge and warrant that they:</p>

                        <ul class="bullet-list">
                            <li>Operate legally and independently within their jurisdiction</li>
                            <li>Hold necessary licenses and permits</li>
                            <li>Maintain required insurance coverage</li>
                            <li>Provide services professionally, safely, and legally</li>
                            <li>Bear full responsibility for their employees, assistants, or subcontractors</li>
                        </ul>

                        <div class="highlight-box">
                            <strong>Notice:</strong> Speeda does not verify Service Provider qualifications or licenses. Clients must exercise caution before contracting.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        Client Obligations
                    </h2>
                    <div class="section-content">
                        <p>Clients must:</p>

                        <ul class="bullet-list">
                            <li>Provide accurate descriptions of required services</li>
                            <li>Ensure safe and lawful access to the service location</li>
                            <li>Refrain from requesting illegal or dangerous services</li>
                            <li>Commit to paying agreed-upon fees directly or through the chosen payment provider</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        Reviews and User-Generated Content
                    </h2>
                    <div class="section-content">
                        <p>Users may post reviews, ratings, photos, or other content ("User Content"). By doing so, they grant Speeda a worldwide, non-exclusive, royalty-free license to use this content to operate, promote, and improve the Platform.</p>

                        <p>Users acknowledge that their content is lawful and does not infringe on others' rights. Speeda reserves the right to remove any inappropriate content or suspend abusive accounts.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        Acceptable Use
                    </h2>
                    <div class="section-content">
                        <p>Users are prohibited from:</p>

                        <ul class="bullet-list">
                            <li>Violating laws or others' rights</li>
                            <li>Posting or requesting illegal, offensive, or dangerous content</li>
                            <li>Impersonating others or providing misleading qualifications</li>
                            <li>Circumventing Speeda's fees or payment system</li>
                            <li>Uploading malicious software or using automated data collection techniques</li>
                        </ul>

                        <p>Speeda may take technical or legal action to prevent any prohibited activity.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        Intellectual Property
                    </h2>
                    <div class="section-content">
                        <p>All Platform content (text, designs, software, logos, graphics, etc.) is owned by or licensed to Speeda and protected under applicable laws. Users may not copy, modify, or redistribute any part without prior written consent.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        Third-Party Links and Services
                    </h2>
                    <div class="section-content">
                        <p>The Platform may contain links or integrations with third-party services (such as payment providers, analytics, or maps). Speeda bears no responsibility for those services or their content, and their use is subject to their own terms and privacy policies.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        Disclaimer of Warranties
                    </h2>
                    <div class="section-content">
                        <p>The Platform is provided "AS IS" and "AS AVAILABLE" without any express or implied warranties. Speeda does not guarantee the accuracy of information, Platform availability, or suitability for any purpose. Speeda bears no responsibility for the quality, timing, or outcomes of services provided by Service Providers.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        Complete Limitation of Liability
                    </h2>
                    <div class="section-content">
                        <div class="subsection">
                            <p><strong>13.1</strong> Speeda operates solely as a technology intermediary between Clients and Service Providers, does not provide any services itself, and is not a party to any agreement or transaction between Users.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.2</strong> Accordingly, Speeda disclaims all liability for any direct, indirect, financial, or moral damages arising from:</p>
                            <ul class="bullet-list">
                                <li>Any agreement or dispute between Client and Service Provider</li>
                                <li>Service performance, delays, or quality</li>
                                <li>Any data, content, or communication between Users</li>
                                <li>Any technical failure, maintenance, or temporary or permanent Platform downtime</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p><strong>13.3</strong> Speeda bears no financial or legal obligations toward any User or third party under any legal theory.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.4</strong> Users acknowledge that they use the Platform at their own risk and that Speeda's role is limited to facilitating communication.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.5</strong> Speeda reserves the complete right to suspend, halt, or terminate the Platform at any time and for any reason without prior notice or compensation.</p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        Indemnification
                    </h2>
                    <div class="section-content">
                        <p>Users agree to indemnify and protect Speeda, its employees, and agents from any claims, losses, or expenses arising from their use of the Platform, violation of these Terms, or disputes with other Users.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        Account Suspension and Termination
                    </h2>
                    <div class="section-content">
                        <p>Speeda may suspend or terminate access or accounts in case of violation of Terms or laws, or at its sole discretion. Users may close their accounts at any time. Provisions relating to intellectual property, liability disclaimers, and governing law remain in effect after termination.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">16</span>
                        Governing Law and Jurisdiction
                    </h2>
                    <div class="section-content">
                        <p>These Terms are governed by the laws of the Province of Ontario and applicable Canadian federal laws. The courts of Ottawa, Ontario have exclusive jurisdiction over any legal disputes related to these Terms.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">17</span>
                        Notices and Communication
                    </h2>
                    <div class="section-content">
                        <p>Users agree to receive notices electronically via email or within the Platform. Electronic actions and consents are considered legally binding signatures.</p>

                        <div class="subsection">
                            <p class="subsection-title">Official Email Addresses:</p>
                            <p><a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">18</span>
                        Modifications
                    </h2>
                    <div class="section-content">
                        <p>Speeda may modify these Terms or update the Platform at any time. Continued use of the Platform after posting updates constitutes acceptance of the modified Terms.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">19</span>
                        Languages
                    </h2>
                    <div class="section-content">
                        <p>The Platform is available in English, French, and Arabic. In case of any conflict between versions, the English version shall prevail unless otherwise required by law.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">20</span>
                        General Provisions
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>These Terms and the Privacy Policy constitute the entire agreement between Users and Speeda</li>
                            <li>No waiver of any provision constitutes a continuing waiver of others</li>
                            <li>If any provision is found invalid, it shall be modified to the minimum extent necessary while other provisions remain in effect</li>
                            <li>Users may not assign these Terms without written consent, while Speeda may transfer them to an affiliate or legal successor</li>
                        </ul>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>Contact Information</h3>
                    <p><strong>Bnine General Trading Inc. (Speeda)</strong></p>
                    <p>Ottawa, Ontario, Canada</p>
                    <p>Email: <a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                    <p>Website: <a href="https://www.Speeda.ca" target="_blank">www.Speeda.ca</a></p>
                </div>

@elseif(app()->getLocale() === 'ar')
                <p class="last-updated">آخر تحديث: 23 أكتوبر 2025</p>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        التعريف والأطراف
                    </h2>
                    <div class="section-content">
                        <p>تُشكّل هذه الشروط والأحكام ("الشروط") اتفاقية قانونية مُلزمة بينك وبين شركة Bnine General Trading Inc.، المالكة والمشغّلة لمنصة Speeda ("Speeda"، "نحن"، "لنا"، أو "خاصتنا")، ومقرّها في أوتاوا، أونتاريو، كندا. تُنظّم هذه الشروط استخدامك لموقع www.Speeda.ca وأي خدمات رقمية ذات صلة (يُشار إليها مجتمعة بـ "المنصة").</p>

                        <div class="definition-list">
                            <div class="definition-item">
                                <span class="definition-term">العميل:</span> أي فرد أو شركة تبحث عن مزوّد خدمة عبر المنصة.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">مزوّد الخدمة:</span> أي فرد أو شركة تُقدّم خدمات بشكل مستقل عبر المنصة.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">المستخدم:</span> يشمل كلًّا من العملاء ومزوّدي الخدمات.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">اتفاقية الخدمة:</span> العقد أو التفاهم المباشر بين العميل ومزوّد الخدمة بشأن خدمة محددة.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">مزود خدمات الدفع (PSP):</span> أي جهة خارجية لمعالجة المدفوعات (مثل PayPal أو التحويل الإلكتروني أو غيرها).
                            </div>
                        </div>

                        <p>من خلال الوصول إلى المنصة أو استخدامها، فإنك توافق على هذه الشروط وعلى سياسة الخصوصية الخاصة بنا. وفي حال عدم موافقتك، يجب عليك التوقف فورًا عن استخدام المنصة.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        الأهلية وإنشاء الحساب
                    </h2>
                    <div class="section-content">
                        <p>يجب أن يكون المستخدمون قد أتمّوا 18 عامًا على الأقل وأن يكونوا مؤهلين قانونيًا لإبرام العقود. ويُطلب منهم تقديم معلومات دقيقة ومُحدّثة، كما يتحمّلون مسؤولية الحفاظ على سرّية بيانات الدخول وجميع أنشطة الحساب. تحتفظ Speeda بحق تعليق أو رفض أو إنهاء أي حساب لحماية المستخدمين أو الامتثال للقانون.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        طبيعة دور Speeda
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
                        استخدام المنصة واتفاقيات الخدمة
                    </h2>
                    <div class="section-content">
                        <p>يختار العملاء مزوّدي الخدمات بحرّية استنادًا إلى المعلومات المتاحة على المنصة. وبمجرد توصّل العميل ومزوّد الخدمة إلى اتفاق، تُبرم اتفاقية الخدمة حصريًا بينهما. ولا تُعتبر Speeda طرفًا في أي اتفاقية خدمة، كما لا تتحمّل أي مسؤولية قانونية أو مالية عن أدائها أو نتائجها أو النزاعات المتعلقة بها.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        الاشتراكات والرسوم والمدفوعات والإلغاء
                    </h2>
                    <div class="section-content">
                        <p>تعمل Speeda وفق نظام اشتراك شهري يمنح المستخدمين إمكانية الوصول إلى ميزات وخدمات المنصة.</p>

                        <ul class="bullet-list">
                            <li>جميع الرسوم غير قابلة للاسترداد كليًا أو جزئيًا</li>
                            <li>يمكن للمستخدمين إلغاء اشتراكهم في أي وقت، ويُصبح الإلغاء ساريًا من بداية دورة الفوترة التالية</li>
                            <li>لا تُرد أي مبالغ عن الفترات غير المستخدمة</li>
                            <li>يمكن سداد المدفوعات عبر التحويل الإلكتروني أو PayPal أو أي مزود دفع آخر تدعمه Speeda</li>
                            <li>باستخدام أي مزود دفع، يوافق المستخدم على شروطه ويُقرّ بأن Speeda ليست بنكًا ولا وكيل ضمان</li>
                            <li>قد تُضاف الضرائب المطبقة (مثل HST/GST/PST) حسب القانون</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        التزامات مزوّدي الخدمات
                    </h2>
                    <div class="section-content">
                        <p>يُقرّ مزوّدو الخدمات ويضمنون أنهم:</p>

                        <ul class="bullet-list">
                            <li>يعملون بصورة قانونية ومستقلة ضمن نطاقهم القضائي</li>
                            <li>يحملون التراخيص والتصاريح اللازمة</li>
                            <li>يحتفظون بالتأمينات المطلوبة</li>
                            <li>يُقدّمون الخدمات باحترافية وأمان وبشكل قانوني</li>
                            <li>يتحمّلون كامل المسؤولية عن موظفيهم أو مساعدينهم أو متعاقديهم الفرعيين</li>
                        </ul>

                        <div class="highlight-box">
                            <strong>تنويه:</strong> لا تتحقق Speeda من مؤهلات مزوّدي الخدمات أو تراخيصهم. ويتعيّن على العملاء توخّي الحذر قبل التعاقد.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        التزامات العملاء
                    </h2>
                    <div class="section-content">
                        <p>يجب على العملاء:</p>

                        <ul class="bullet-list">
                            <li>تقديم أوصاف دقيقة للخدمات المطلوبة</li>
                            <li>ضمان الوصول الآمن والمشروع إلى موقع الخدمة</li>
                            <li>الامتناع عن طلب خدمات غير قانونية أو خطرة</li>
                            <li>الالتزام بسداد المدفوعات المتفق عليها مباشرة أو عبر مزود الدفع المختار</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        المراجعات والمحتوى الذي ينشئه المستخدم
                    </h2>
                    <div class="section-content">
                        <p>يجوز للمستخدمين نشر مراجعات أو تقييمات أو صور أو محتوى آخر ("محتوى المستخدم"). وبقيامهم بذلك، يمنحون Speeda ترخيصًا عالميًا غير حصري وخاليًا من الرسوم لاستخدام هذا المحتوى لتشغيل المنصة وترويجها وتحسينها.</p>

                        <p>يُقرّ المستخدمون بأن محتواهم قانوني ولا ينتهك حقوق الآخرين، وتحتفظ Speeda بالحق في إزالة أي محتوى غير لائق أو تعليق الحسابات المسيئة.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        الاستخدام المقبول
                    </h2>
                    <div class="section-content">
                        <p>يُحظر على المستخدمين:</p>

                        <ul class="bullet-list">
                            <li>انتهاك القوانين أو حقوق الغير</li>
                            <li>نشر أو طلب محتوى غير قانوني أو مسيء أو خطير</li>
                            <li>انتحال هوية الآخرين أو تقديم مؤهلات مضلّلة</li>
                            <li>التحايل على رسوم Speeda أو نظام الدفع الخاص بها</li>
                            <li>تحميل برمجيات ضارة أو استخدام تقنيات جمع بيانات تلقائية</li>
                        </ul>

                        <p>يجوز لـ Speeda اتخاذ إجراءات تقنية أو قانونية لمنع أي نشاط محظور.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        الملكية الفكرية
                    </h2>
                    <div class="section-content">
                        <p>جميع محتويات المنصة (النصوص، التصاميم، البرامج، الشعارات، الرسومات، إلخ) مملوكة أو مرخّصة لـ Speeda ومحمية بموجب القوانين المعمول بها. ولا يجوز للمستخدمين نسخ أو تعديل أو إعادة توزيع أي جزء منها دون موافقة كتابية مسبقة.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        الروابط والخدمات التابعة لجهات خارجية
                    </h2>
                    <div class="section-content">
                        <p>قد تحتوي المنصة على روابط أو تكاملات مع خدمات أطراف ثالثة (مثل مزوّدي الدفع أو التحليلات أو الخرائط). ولا تتحمّل Speeda أي مسؤولية عن تلك الخدمات أو محتواها، ويخضع استخدامها لشروطها وسياسات الخصوصية الخاصة بها.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        إخلاء المسؤولية عن الضمانات
                    </h2>
                    <div class="section-content">
                        <p>تُقدَّم المنصة "كما هي" و"حسب التوافر"، دون أي ضمانات صريحة أو ضمنية. لا تضمن Speeda دقة المعلومات أو توافر المنصة أو ملاءمتها لأي غرض. كما لا تتحمّل مسؤولية جودة أو توقيت أو نتائج الخدمات المقدَّمة من مزوّدي الخدمات.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        إخلاء المسؤولية الكاملة
                    </h2>
                    <div class="section-content">
                        <div class="subsection">
                            <p><strong>13.1</strong> تعمل Speeda فقط كوسيط تكنولوجي بين العملاء ومزوّدي الخدمات، ولا تُقدّم أي خدمات بنفسها، ولا تُعد طرفًا في أي اتفاق أو معاملة بين المستخدمين.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.2</strong> بناءً على ذلك، تُخلي Speeda مسؤوليتها عن أي أضرار مباشرة أو غير مباشرة أو مالية أو معنوية ناجمة عن:</p>
                            <ul class="bullet-list">
                                <li>أي اتفاق أو نزاع بين العميل ومزوّد الخدمة</li>
                                <li>أداء الخدمة أو تأخرها أو جودتها</li>
                                <li>أي بيانات أو محتوى أو تواصل بين المستخدمين</li>
                                <li>أي عطل تقني أو صيانة أو توقف مؤقت أو دائم للمنصة</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p><strong>13.3</strong> لا تتحمّل Speeda أي التزامات مالية أو قانونية تجاه أي مستخدم أو طرف ثالث تحت أي نظرية قانونية.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.4</strong> يقرّ المستخدمون بأنهم يستخدمون المنصة على مسؤوليتهم الخاصة، وأن دور Speeda يقتصر على تسهيل التواصل.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.5</strong> تحتفظ Speeda بالحق الكامل في تعليق أو إيقاف أو إنهاء المنصة في أي وقت ولأي سبب دون إشعار مسبق أو تعويض.</p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        التعويض
                    </h2>
                    <div class="section-content">
                        <p>يوافق المستخدمون على تعويض وحماية Speeda وموظفيها ووكلائها من أي مطالبات أو خسائر أو نفقات ناتجة عن استخدامهم للمنصة أو مخالفتهم لهذه الشروط أو نزاعاتهم مع مستخدمين آخرين.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        التعليق وإنهاء الحسابات
                    </h2>
                    <div class="section-content">
                        <p>يجوز لـ Speeda تعليق أو إنهاء الوصول أو الحسابات في حال مخالفة الشروط أو القوانين، أو وفق تقديرها الخاص. ويمكن للمستخدمين إغلاق حساباتهم في أي وقت. وتبقى البنود المتعلقة بالملكية الفكرية وإخلاء المسؤولية والقانون الواجب التطبيق سارية بعد الإنهاء.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">16</span>
                        القانون الواجب التطبيق والاختصاص القضائي
                    </h2>
                    <div class="section-content">
                        <p>تخضع هذه الشروط لقوانين مقاطعة أونتاريو والقوانين الفيدرالية الكندية السارية. وتتمتع محاكم أوتاوا، أونتاريو بالاختصاص الحصري في أي نزاعات قانونية متعلقة بهذه الشروط.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">17</span>
                        الإشعارات والتواصل
                    </h2>
                    <div class="section-content">
                        <p>يوافق المستخدمون على تلقي الإشعارات إلكترونيًا عبر البريد الإلكتروني أو داخل المنصة، وتُعدّ الإجراءات والموافقات الإلكترونية توقيعات قانونية مُلزمة.</p>

                        <div class="subsection">
                            <p class="subsection-title">عناوين البريد الرسمية:</p>
                            <p><a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">18</span>
                        التعديلات
                    </h2>
                    <div class="section-content">
                        <p>يجوز لـ Speeda تعديل هذه الشروط أو تحديث المنصة في أي وقت. ويُعدّ استمرار استخدام المنصة بعد نشر التحديثات موافقة على الشروط المعدّلة.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">19</span>
                        اللغات
                    </h2>
                    <div class="section-content">
                        <p>تتوفر المنصة باللغات الإنجليزية والفرنسية والعربية. وفي حال وجود أي تعارض بين النسخ، تكون النسخة الإنجليزية هي المعتمدة ما لم ينص القانون على خلاف ذلك.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">20</span>
                        أحكام عامة
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>تُشكّل هذه الشروط وسياسة الخصوصية كامل الاتفاق بين المستخدمين وSpeeda</li>
                            <li>لا يُعتبر أي تنازل عن بندٍ ما تنازلاً مستمرًا عن غيره</li>
                            <li>إذا ثبت بطلان أي بند، يُعدّل بالحد الأدنى اللازم مع بقاء باقي البنود سارية</li>
                            <li>لا يجوز للمستخدمين التنازل عن هذه الشروط دون موافقة كتابية، بينما يجوز لـ Speeda نقلها إلى كيان تابع أو خلف قانوني</li>
                        </ul>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>معلومات الاتصال</h3>
                    <p><strong>Bnine General Trading Inc. (Speeda)</strong></p>
                    <p>أوتاوا، أونتاريو، كندا</p>
                    <p>البريد الإلكتروني: <a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                    <p>الموقع الإلكتروني: <a href="https://www.Speeda.ca" target="_blank">www.Speeda.ca</a></p>
                </div>

@else
                <p class="last-updated">Dernière mise à jour : 23 octobre 2025</p>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">1</span>
                        Définitions et Parties
                    </h2>
                    <div class="section-content">
                        <p>Les présentes Conditions d'utilisation (« Conditions ») constituent un accord juridiquement contraignant entre vous et Bnine General Trading Inc., propriétaire et exploitant de la plateforme Speeda (« Speeda », « nous », « notre » ou « nos »), dont le siège se trouve à Ottawa, Ontario, Canada. Ces Conditions régissent votre utilisation de www.Speeda.ca et de tous les services numériques connexes (collectivement désignés comme la « Plateforme »).</p>

                        <div class="definition-list">
                            <div class="definition-item">
                                <span class="definition-term">Client :</span> Toute personne ou entreprise recherchant un prestataire de services via la Plateforme.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Prestataire de services :</span> Toute personne ou entreprise offrant des services de manière indépendante via la Plateforme.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Utilisateur :</span> Comprend à la fois les Clients et les Prestataires de services.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Accord de service :</span> Le contrat ou l'entente directe entre un Client et un Prestataire de services concernant un service spécifique.
                            </div>
                            <div class="definition-item">
                                <span class="definition-term">Fournisseur de services de paiement (PSP) :</span> Tout processeur de paiement tiers (tel que PayPal, virement électronique ou autres).
                            </div>
                        </div>

                        <p>En accédant ou en utilisant la Plateforme, vous acceptez ces Conditions et notre Politique de confidentialité. Si vous n'êtes pas d'accord, vous devez immédiatement cesser d'utiliser la Plateforme.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">2</span>
                        Éligibilité et Création de Compte
                    </h2>
                    <div class="section-content">
                        <p>Les utilisateurs doivent avoir au moins 18 ans et être légalement qualifiés pour conclure des contrats. Les utilisateurs sont tenus de fournir des informations exactes et à jour et sont responsables du maintien de la confidentialité des identifiants de connexion et de toutes les activités du compte. Speeda se réserve le droit de suspendre, refuser ou résilier tout compte pour protéger les utilisateurs ou se conformer à la loi.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">3</span>
                        Nature du Rôle de Speeda
                    </h2>
                    <div class="section-content">
                        <div class="highlight-box">
                            <strong>Important :</strong> Speeda opère uniquement comme intermédiaire technologique reliant les Clients et les Prestataires de services. Speeda ne fournit pas de services elle-même, n'emploie pas de Prestataires de services, ne fixe pas les prix et ne supervise pas la qualité, le calendrier ou les résultats des services.
                        </div>
                        <p>Aucune disposition des présentes Conditions ne crée une relation d'emploi, d'agence ou de partenariat entre Speeda et tout Utilisateur.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">4</span>
                        Utilisation de la Plateforme et Accords de Service
                    </h2>
                    <div class="section-content">
                        <p>Les Clients choisissent librement les Prestataires de services en fonction des informations disponibles sur la Plateforme. Une fois qu'un Client et un Prestataire de services parviennent à un accord, l'Accord de service est formé exclusivement entre eux. Speeda n'est pas partie à aucun Accord de service et n'assume aucune responsabilité légale ou financière pour son exécution, ses résultats ou les litiges en découlant.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">5</span>
                        Abonnements, Frais, Paiements et Annulation
                    </h2>
                    <div class="section-content">
                        <p>Speeda fonctionne selon un système d'abonnement mensuel qui accorde aux Utilisateurs l'accès aux fonctionnalités et services de la Plateforme.</p>

                        <ul class="bullet-list">
                            <li>Tous les frais sont entièrement non remboursables, en tout ou en partie</li>
                            <li>Les utilisateurs peuvent annuler leur abonnement à tout moment, l'annulation prenant effet au début du prochain cycle de facturation</li>
                            <li>Aucun remboursement n'est fourni pour les périodes non utilisées</li>
                            <li>Les paiements peuvent être effectués par virement électronique, PayPal ou tout autre fournisseur de paiement pris en charge par Speeda</li>
                            <li>En utilisant un fournisseur de paiement, les Utilisateurs acceptent ses conditions et reconnaissent que Speeda n'est pas une banque ni un agent d'entiercement</li>
                            <li>Les taxes applicables (telles que HST/GST/PST) peuvent être ajoutées conformément à la loi</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">6</span>
                        Obligations des Prestataires de Services
                    </h2>
                    <div class="section-content">
                        <p>Les Prestataires de services reconnaissent et garantissent qu'ils :</p>

                        <ul class="bullet-list">
                            <li>Opèrent légalement et de manière indépendante dans leur juridiction</li>
                            <li>Détiennent les licences et permis nécessaires</li>
                            <li>Maintiennent la couverture d'assurance requise</li>
                            <li>Fournissent des services de manière professionnelle, sûre et légale</li>
                            <li>Assument l'entière responsabilité de leurs employés, assistants ou sous-traitants</li>
                        </ul>

                        <div class="highlight-box">
                            <strong>Avis :</strong> Speeda ne vérifie pas les qualifications ou les licences des Prestataires de services. Les Clients doivent faire preuve de prudence avant de contracter.
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">7</span>
                        Obligations des Clients
                    </h2>
                    <div class="section-content">
                        <p>Les Clients doivent :</p>

                        <ul class="bullet-list">
                            <li>Fournir des descriptions précises des services requis</li>
                            <li>Assurer un accès sûr et légal au lieu de service</li>
                            <li>S'abstenir de demander des services illégaux ou dangereux</li>
                            <li>S'engager à payer les frais convenus directement ou par l'intermédiaire du fournisseur de paiement choisi</li>
                        </ul>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">8</span>
                        Avis et Contenu Généré par l'Utilisateur
                    </h2>
                    <div class="section-content">
                        <p>Les utilisateurs peuvent publier des avis, des évaluations, des photos ou d'autres contenus (« Contenu utilisateur »). Ce faisant, ils accordent à Speeda une licence mondiale, non exclusive et gratuite pour utiliser ce contenu afin d'exploiter, promouvoir et améliorer la Plateforme.</p>

                        <p>Les utilisateurs reconnaissent que leur contenu est légal et ne viole pas les droits d'autrui. Speeda se réserve le droit de supprimer tout contenu inapproprié ou de suspendre les comptes abusifs.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">9</span>
                        Utilisation Acceptable
                    </h2>
                    <div class="section-content">
                        <p>Il est interdit aux utilisateurs de :</p>

                        <ul class="bullet-list">
                            <li>Violer les lois ou les droits d'autrui</li>
                            <li>Publier ou demander du contenu illégal, offensant ou dangereux</li>
                            <li>Usurper l'identité d'autrui ou fournir des qualifications trompeuses</li>
                            <li>Contourner les frais de Speeda ou son système de paiement</li>
                            <li>Télécharger des logiciels malveillants ou utiliser des techniques de collecte de données automatisées</li>
                        </ul>

                        <p>Speeda peut prendre des mesures techniques ou légales pour empêcher toute activité interdite.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">10</span>
                        Propriété Intellectuelle
                    </h2>
                    <div class="section-content">
                        <p>Tout le contenu de la Plateforme (textes, designs, logiciels, logos, graphiques, etc.) est détenu ou licencié par Speeda et protégé par les lois applicables. Les utilisateurs ne peuvent copier, modifier ou redistribuer aucune partie sans consentement écrit préalable.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">11</span>
                        Liens et Services Tiers
                    </h2>
                    <div class="section-content">
                        <p>La Plateforme peut contenir des liens ou des intégrations avec des services tiers (tels que des fournisseurs de paiement, d'analyse ou de cartes). Speeda n'assume aucune responsabilité pour ces services ou leur contenu, et leur utilisation est soumise à leurs propres conditions et politiques de confidentialité.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">12</span>
                        Exclusion de Garanties
                    </h2>
                    <div class="section-content">
                        <p>La Plateforme est fournie "TELLE QUELLE" et "TELLE QUE DISPONIBLE" sans aucune garantie expresse ou implicite. Speeda ne garantit pas l'exactitude des informations, la disponibilité de la Plateforme ou son adéquation à un usage particulier. Speeda n'assume aucune responsabilité pour la qualité, le calendrier ou les résultats des services fournis par les Prestataires de services.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">13</span>
                        Limitation Complète de Responsabilité
                    </h2>
                    <div class="section-content">
                        <div class="subsection">
                            <p><strong>13.1</strong> Speeda opère uniquement comme intermédiaire technologique entre les Clients et les Prestataires de services, ne fournit aucun service elle-même et n'est partie à aucun accord ou transaction entre les Utilisateurs.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.2</strong> En conséquence, Speeda décline toute responsabilité pour tout dommage direct, indirect, financier ou moral découlant de :</p>
                            <ul class="bullet-list">
                                <li>Tout accord ou litige entre le Client et le Prestataire de services</li>
                                <li>La performance, les retards ou la qualité du service</li>
                                <li>Toutes données, contenus ou communications entre Utilisateurs</li>
                                <li>Toute défaillance technique, maintenance ou interruption temporaire ou permanente de la Plateforme</li>
                            </ul>
                        </div>

                        <div class="subsection">
                            <p><strong>13.3</strong> Speeda n'assume aucune obligation financière ou légale envers tout Utilisateur ou tiers en vertu de quelque théorie juridique que ce soit.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.4</strong> Les utilisateurs reconnaissent qu'ils utilisent la Plateforme à leurs propres risques et que le rôle de Speeda se limite à faciliter la communication.</p>
                        </div>

                        <div class="subsection">
                            <p><strong>13.5</strong> Speeda se réserve le droit complet de suspendre, d'arrêter ou de mettre fin à la Plateforme à tout moment et pour quelque raison que ce soit, sans préavis ni compensation.</p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">14</span>
                        Indemnisation
                    </h2>
                    <div class="section-content">
                        <p>Les utilisateurs acceptent d'indemniser et de protéger Speeda, ses employés et agents contre toute réclamation, perte ou dépense découlant de leur utilisation de la Plateforme, de la violation de ces Conditions ou de litiges avec d'autres Utilisateurs.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">15</span>
                        Suspension et Résiliation de Compte
                    </h2>
                    <div class="section-content">
                        <p>Speeda peut suspendre ou résilier l'accès ou les comptes en cas de violation des Conditions ou des lois, ou à sa seule discrétion. Les utilisateurs peuvent fermer leur compte à tout moment. Les dispositions relatives à la propriété intellectuelle, aux exclusions de responsabilité et au droit applicable restent en vigueur après la résiliation.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">16</span>
                        Droit Applicable et Juridiction
                    </h2>
                    <div class="section-content">
                        <p>Ces Conditions sont régies par les lois de la province de l'Ontario et les lois fédérales canadiennes applicables. Les tribunaux d'Ottawa, Ontario, ont compétence exclusive sur tout litige juridique lié à ces Conditions.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">17</span>
                        Notifications et Communication
                    </h2>
                    <div class="section-content">
                        <p>Les utilisateurs acceptent de recevoir des notifications électroniquement par courriel ou au sein de la Plateforme. Les actions et consentements électroniques sont considérés comme des signatures juridiquement contraignantes.</p>

                        <div class="subsection">
                            <p class="subsection-title">Adresses E-mail Officielles :</p>
                            <p><a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">18</span>
                        Modifications
                    </h2>
                    <div class="section-content">
                        <p>Speeda peut modifier ces Conditions ou mettre à jour la Plateforme à tout moment. La poursuite de l'utilisation de la Plateforme après la publication des mises à jour constitue l'acceptation des Conditions modifiées.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">19</span>
                        Langues
                    </h2>
                    <div class="section-content">
                        <p>La Plateforme est disponible en anglais, français et arabe. En cas de conflit entre les versions, la version anglaise prévaudra, sauf disposition contraire de la loi.</p>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">
                        <span class="section-number">20</span>
                        Dispositions Générales
                    </h2>
                    <div class="section-content">
                        <ul class="bullet-list">
                            <li>Ces Conditions et la Politique de confidentialité constituent l'intégralité de l'accord entre les Utilisateurs et Speeda</li>
                            <li>Aucune renonciation à une disposition ne constitue une renonciation continue à d'autres</li>
                            <li>Si une disposition est jugée invalide, elle sera modifiée dans la mesure minimale nécessaire tandis que les autres dispositions resteront en vigueur</li>
                            <li>Les utilisateurs ne peuvent céder ces Conditions sans consentement écrit, tandis que Speeda peut les transférer à une société affiliée ou à un successeur légal</li>
                        </ul>
                    </div>
                </div>

                <div class="contact-box">
                    <h3>Coordonnées</h3>
                    <p><strong>Bnine General Trading Inc. (Speeda)</strong></p>
                    <p>Ottawa, Ontario, Canada</p>
                    <p>Email: <a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
                    <p>Site Web: <a href="https://www.Speeda.ca" target="_blank">www.Speeda.ca</a></p>
                </div>

@endif
        </div>
    </div>
    </body>
</html>
