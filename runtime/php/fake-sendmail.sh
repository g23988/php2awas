#!/bin/sh
# 取代 sendmail：把 PHP mail() 的完整內容附加到 /var/mail-out/mail.log
# 每封信前後加上分隔線，測試程式用 "=====MAIL=====" 切開
LOG=/var/mail-out/mail.log
{
  echo "=====MAIL====="
  cat
  echo ""
} >> "$LOG"
exit 0
