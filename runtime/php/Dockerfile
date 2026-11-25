# ---------------------------------------------------------------
# 舊系統執行環境：PHP 5.6 + Apache（模擬當年的 LAMP 主機）
#
# 原系統當年跑在 PHP 4.4，但 PHP4 已經沒有可用的 Docker image。
# 程式碼本身維持 PHP4 語法；這裡用 PHP 5.6（最後一個還有 mysql_* 擴充的版本）
# 讓它能實際執行，作為「改寫前行為」的對照基準。
# ---------------------------------------------------------------
FROM php:5.6-apache

# mysql_* 擴充（CodeIgniter 1.7 的 mysql driver 需要）
RUN docker-php-ext-install mysql

# 假的 sendmail：把 mail() 寄出的信寫進 /var/mail-out/mail.log，方便測試檢查
COPY fake-sendmail.sh /usr/local/bin/fake-sendmail
RUN chmod +x /usr/local/bin/fake-sendmail

COPY php.ini /usr/local/etc/php/php.ini

# CI 1.7 的 cache / logs 目錄需要可寫入
WORKDIR /var/www/html
