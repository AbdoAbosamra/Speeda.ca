دليل النشر خطوة‑بخطوة (جاهز للنسخ والالتزام)

مُلخّص: سنجهّز الحزمة محليًا ثم نرفعها إلى الـ VPS ونفكها داخل `/var/www/<your-project>`، ثم نثبت الاعتمادات على السيرفر (إن لزم)، نضبط الأذونات، نعدّ Nginx، ونفعّل SSL.

الخطوات المحلية (على جهازك - داخل مجلد المشروع):

1) جهّز المفتاح العام إذا لم تفعل:
```powershell
ssh-keygen -t ed25519 -C "you@example.com"
type $env:USERPROFILE\.ssh\id_ed25519.pub
```
انسخ النص الناتج.

2) أضف المفتاح العام إلى VPS عبر Hostinger panel أو مباشرة (مثال من PowerShell):
```powershell
type $env:USERPROFILE\.ssh\id_ed25519.pub | ssh root@<VPS_IP> "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys"
```

3) من جهازك: شغّل سكربت التحزيم لابتكار الأرشيف الجاهز للرفع:
```bash
# على Windows استخدم Git Bash أو WSL، أو على Linux/macOS استخدم الطرفية
cd /path/to/your/project
chmod +x deploy/prepare_deploy.sh
./deploy/prepare_deploy.sh my-project-name
# الناتج: my-project-name_YYYY-MM-DD_HHMMSS.tar.gz
```

4) ارفع الأرشيف للخادم (مثال عبر scp):
```powershell
scp my-project-name_*.tar.gz deployuser@<VPS_IP>:/home/deployuser/
```


الخطوات على الـ VPS (بعد رفع الأرشيف) — شغّل هذه الأوامر عبر SSH كمستخدم `deployuser` مع sudo:

1) تسجيل دخول:
```bash
ssh deployuser@<VPS_IP>
```

2) إنشاء المجلد المستهدف ووضع الأرشيف:
```bash
sudo mkdir -p /var/www/<your-project>
sudo chown deployuser:deployuser /var/www/<your-project>
cd /var/www/<your-project>
# إن النسخة في /home/deployuser
mv /home/deployuser/my-project-name_*.tar.gz ./
```

3) فك الأرشيف:
```bash
tar -xzf my-project-name_*.tar.gz -C /var/www/<your-project>
```

4) تثبيت الاعتمادات على السيرفر (اختياري إن شملت vendor في الحزمة، لكن سكربتنا استبعد vendor):
```bash
cd /var/www/<your-project>
composer install --no-dev --optimize-autoloader
```

5) ضبط الأذونات وإنشاء storage link:
```bash
sudo chown -R www-data:www-data /var/www/<your-project>
sudo find /var/www/<your-project> -type f -exec chmod 644 {} \;
sudo find /var/www/<your-project> -type d -exec chmod 755 {} \;
php artisan storage:link
chmod 600 .env
```

6) إعداد قاعدة البيانات (داخل mysql):
```bash
sudo mysql -u root -p
# داخل MySQL:
CREATE DATABASE your_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'your_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL ON your_db.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```
ثم شغّل المهاجرات:
```bash
php artisan migrate --force
php artisan db:seed --force  # إن احتجت
```

7) تهيئة Nginx: انسخ ملف المثال إلى sites-available وعدّله للمجال
```bash
sudo cp /home/deployuser/<repo>/deploy/nginx.example.conf /etc/nginx/sites-available/<your-project>
sudo nano /etc/nginx/sites-available/<your-project>  # عدّل server_name وroot وfastcgi_pass
sudo ln -s /etc/nginx/sites-available/<your-project> /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

8) تفعيل SSL عبر Certbot:
```bash
sudo certbot --nginx -d example.com -d www.example.com
```

9) إعداد Cron scheduler:
```bash
sudo crontab -e
# أضف السطر التالي:
* * * * * cd /var/www/<your-project> && php artisan schedule:run >> /dev/null 2>&1
```

10) إعداد Supervisor للـ queues:
```bash
sudo cp /home/deployuser/<repo>/deploy/supervisor.example.conf /etc/supervisor/conf.d/<your-project>-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```


سجلات وفحص سريع:
```bash
sudo tail -f /var/log/nginx/<your-project>.error.log
tail -f /var/www/<your-project>/storage/logs/laravel.log
```

نقاط أمان:
- لا ترفع `.env` عبر قناة غير آمنة. استخدم `scp` أو أنشئ `.env` يدويًا على الخادم.
- عدّل `ssh` لإلغاء PermitRootLogin بعد تثبيت المستخدم.
- استخدم كلمات مرور قوية لقاعدة البيانات.

إذا تريد، أستطيع الآن:
- تجهيز الحزمة محليًا (شغّل `./deploy/prepare_deploy.sh my-project-name`) ثم أقدّم أوامر `scp`/`ssh` كاملة لاستكمال النشر، أو
- أقدّم سكربت نشر تلقائي كامل لتشغيله على الـ VPS (يتضمن استخراج الأرشيف، composer install، أذونات، reload nginx). 

اختر أي خيار تفضّل أنفّذه الآن.
