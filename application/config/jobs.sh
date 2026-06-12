# Legacy combined crontab — split by deployment:
#
#   jobs-health.sh    MOH / attend.health.go.ug  (BioTime, iHRIS, dashboard cache)
#   jobs-education.sh Education / educationattend (summaries + facility cache only)
#
# Prefer: * * * * * cd $APP_ROOT && php index.php jobs master
# The master scheduler reads deployment_type from the setting table.
