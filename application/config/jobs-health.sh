# MOH / Health deployment crontab (attend.health.go.ug)
# BioTime + iHRIS sync, dashboard cache warm, summary reports.
#
# Install: crontab -e  (paste lines below, adjust APP_ROOT)
#
# APP_ROOT=/var/www/attend.health.go.ug

# Master scheduler (replaces individual curl lines for biotimejobs/*)
* * * * * cd /var/www/attend.health.go.ug && /usr/bin/php index.php jobs master >> /var/log/attend-jobs-master.log 2>&1

# Weekly Switch Facility cache rebuild (ihrisdata → JSON / Redis)
0 0 * * 0 /var/www/attend.health.go.ug/application/modules/cronjobs/scripts/facility_switch_cache_cron.sh

# Repo sync
*/5 * * * * cd /var/www/attend.health.go.ug && sudo git pull --no-edit >> /var/log/attend-pull.log 2>&1
*/5 * * * * cd /var/www/attend.health.go.ug/demo && sudo git pull --no-edit >> /var/log/attend-demo-pull.log 2>&1
15 * * * * cd /var/www/attend.health.go.ug/safe_mama && sudo git pull --no-edit

# Related staff portal (separate app)
* 20 * * * curl https://attend.health.go.ug/staff/person/get_ihrisdata
* * * * * cd /var/www/staff && php index.php person send_mails

# BioTime supervisor (attendance fetch fallback)
0 */4 * * * /usr/bin/supervisorctl start biotimejobs
