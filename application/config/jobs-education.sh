# Education deployment crontab (pmdu-data.opm.go.ug/educationattend)
# No BioTime or iHRIS sync — mobile attendance only.
# Runs summary reports and facility dropdown cache from local employee tables.
#
# Install: crontab -e  (paste lines below, adjust APP_ROOT)
#
# APP_ROOT=/var/www/html/educationattend

# Master scheduler (education branch: summaries + facility cache only)
* * * * * cd /var/www/html/educationattend && /usr/bin/php index.php jobs master >> /var/log/educationattend-jobs-master.log 2>&1

# Weekly Switch Facility cache rebuild (employee_districts / employee_facility → JSON / Redis)
0 0 * * 0 /var/www/html/educationattend/application/modules/cronjobs/scripts/facility_switch_cache_cron.sh

# Repo sync (if used on this server)
*/5 * * * * cd /var/www/html/educationattend && sudo git pull --no-edit >> /var/log/educationattend-pull.log 2>&1
