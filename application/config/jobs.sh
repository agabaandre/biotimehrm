
# m h  dom mon dow   command
#get data from duty roster to attendance
*/20 * * * * curl https://attend.health.go.ug/biotimejobs/terminals
37 7 * * * curl https://attend.health.go.ug/biotimejobs/rostatoAttend
#get enrolled users in biotime and ihris data who have cardnumber
*/30 * * * * curl https://attend.health.go.ug/biotimejobs/saveEnrolled
0 */7 * * * curl -k  https://attend.health.go.ug/biotimejobs/get_ihrisdata
#*/50 * * * * curl https://attend.health.go.ug/biotimejobs/get_ucmbdata
# jobs create new ihrisdata facilities, jobs, departments
#getbiotime data 
05 7 * * * curl https://attend.health.go.ug/biotimejobs/biotime_jobs
*/45 * * * * curl https://attend.health.go.ug/biotimejobs/biotimeFacilities
15 7 * * * curl https://attend.health.go.ug/biotimejobs/biotimedepartments
40 7 * * * curl https://attend.health.go.ug/biotimejobs/biotime_employees

#enroll new users
*/55 * * * * curl https://attend.health.go.ug/biotimejobs/multiple_new_users
#TRANSFER EMPLOYEES
*/35 * * * * curl https://attend.health.go.ug/biotimejobs/transfer_employees
#get clockin info
# everyday at 11 a
#clockin/cout/markattendance called automatically after fetching time_logs
0 */4 * * * curl -k https://attend.health.go.ug/biotimejobs/fetch_daily_attendance
#public holidays
#fetch attendance
0 */4 * * * /usr/bin/supervisorctl start biotimejobs
#0 */4 * * * cd /var/www/attend.health.go.ug && php index.php biotimejobs fetch_daily_attendance
0 15 1 * * curl https://attend.health.go.ug/cronjobs/publicdaystoAttend
#auto Rosta /Run after choosing the facilities to auto populate.
0 0 1 * * curl https://attend.health.go.ug/cronjobs/AutoMohRoster
# biotime terminals available
#*/45 * * * * curl https://attend.health.go.ug/biotimejobs/terminals
15 7 * * * curl https://attend.health.go.ug/biotimejobs/biotimedepartments
15 * * * * curl https://attend.health.go.ug/biotime_jobs/cache_dash_Data
#05 23 * * * curl https://attend.health.go.ug/cronjobs/AutoRosterOthers

15 * * * * cd /var/www/attend.health.go.ug/safe_mama && sudo git pull --no-edit

* 20 * * * curl https://attend.health.go.ug/staff/person/get_ihrisdata
* * * * * cd /var/www/staff && php index.php person send_mails

*/5 * * * * cd /var/www/attend.health.go.ug/demo && sudo git pull --no-edit >> /var/log/attend-demo-pull.log 2>&1
*/5 * * * * cd /var/www/attend.health.go.ug && sudo git pull --no-edit >> /var/log/attend-pull.log 2>&1
0 2 */5 * * curl -k https://attend.health.go.ug/index.php/cronjobs/DutyRosterSummaryCron/updateDutyRosterSummary > /dev/null 2>&1
0 */6 * * * curl -k https://attend.health.go.ug/index.php/cronjobs/AttendanceSummaryCron/updateAttendanceSummary > /dev/null 2>&1


