# Attendance Summary Cron Job Setup

This document explains how to set up and configure the automated cron job to update the `person_att_final` table with monthly attendance summaries.

## 🎯 **Overview**

The cron job automatically processes attendance data from the `actuals` table and generates monthly summaries in the `person_att_final` table. It runs daily at 1:00 AM and only processes data starting from May 1st, 2025.

## 📁 **Files Created**

1. **`application/modules/cronjobs/controllers/AttendanceSummaryCron.php`** - Main cron job controller
2. **`application/modules/cronjobs/config/cron_config.php`** - Configuration file
3. **`cron_execute.sh`** - Shell script for system cron execution
4. **`CRON_SETUP_README.md`** - This setup guide

## 🚀 **Setup Instructions**

### **Step 1: Make the Shell Script Executable**

```bash
chmod +x cron_execute.sh
```

### **Step 2: Update Configuration**

Edit `cron_execute.sh` and update the `BASE_URL` variable:

```bash
BASE_URL="http://your-domain.com/attend"  # Change this to your actual domain
```

### **Step 3: Add to System Cron**

Add the following line to your system crontab:

```bash
# Edit crontab
crontab -e

# Add this line (runs daily at 1:00 AM)
0 1 * * * /opt/homebrew/var/www/attend/cron_execute.sh
```

### **Step 4: Create Log Directory**

```bash
mkdir -p /opt/homebrew/var/www/attend/application/logs/cron
chmod 755 /opt/homebrew/var/www/attend/application/logs/cron
```

## ⚙️ **Cron Job Details**

### **Schedule**
- **Expression**: `0 1 * * *`
- **Frequency**: Daily at 1:00 AM
- **Start Date**: May 1st, 2025
- **Timezone**: Africa/Nairobi

### **What It Does**
1. **Data Processing**: Aggregates daily attendance records into monthly summaries
2. **Table Population**: Inserts/updates records in `person_att_final`
3. **Duplicate Prevention**: Clears existing data before inserting new records
4. **Timestamp Tracking**: Updates `last_gen` timestamp for monitoring

### **Data Sources**
- **`actuals`** table - Daily attendance records
- **`ihrisdata`** table - Employee information
- **Schedule IDs**: 22 (Present), 24 (Off-duty), 25 (Leave), 23 (Request), 26 (Absent), 27 (Holiday)

## 🔧 **Testing & Monitoring**

### **Manual Execution (Testing)**

```bash
# Test the cron job manually
curl -X GET "http://your-domain.com/attend/cronjobs/AttendanceSummaryCron/updateAttendanceSummary"
```

### **Check Status**

```bash
# Check last execution status
curl -X GET "http://your-domain.com/attend/cronjobs/AttendanceSummaryCron/getStatus"
```

### **View Logs**

```bash
# View cron job logs
tail -f /opt/homebrew/var/www/attend/application/logs/cron/attendance_summary_$(date +%Y%m%d).log

# View all cron logs
ls -la /opt/homebrew/var/www/attend/application/logs/cron/
```

## 📊 **Database Tables**

### **Input Tables**
- **`actuals`** - Daily attendance records
- **`ihrisdata`** - Employee master data

### **Output Table**
- **`person_att_final`** - Monthly attendance summaries

### **Tracking Table**
- **`system_tables_timestamp`** - Cron job execution timestamps

## 🛠️ **Troubleshooting**

### **Common Issues**

1. **Permission Denied**
   ```bash
   chmod +x cron_execute.sh
   chmod 755 /opt/homebrew/var/www/attend/application/logs/cron
   ```

2. **Cron Job Not Running**
   ```bash
   # Check cron service status
   sudo systemctl status cron
   
   # Check cron logs
   sudo tail -f /var/log/cron
   ```

3. **HTTP Request Failing**
   - Verify `BASE_URL` in `cron_execute.sh`
   - Check web server is running
   - Verify application is accessible

4. **Database Connection Issues**
   - Check database credentials in `application/config/database.php`
   - Verify database server is running
   - Check database permissions

### **Debug Mode**

Enable debug logging in `application/config/config.php`:

```php
$config['log_threshold'] = 4; // Log everything
```

## 📈 **Performance Considerations**

- **Execution Time**: Limited to 5 minutes maximum
- **Data Volume**: Processes entire year of data daily
- **Memory Usage**: Optimized queries to minimize memory footprint
- **Locking**: Uses PID file to prevent concurrent execution

## 🔒 **Security Features**

- **AJAX Protection**: Manual trigger methods require AJAX requests
- **Logging**: Comprehensive logging of all operations
- **Error Handling**: Graceful error handling and reporting
- **PID Protection**: Prevents multiple instances from running simultaneously

## 📝 **Customization**

### **Change Start Date**
Edit `AttendanceSummaryCron.php`:

```php
$start_date = '2025-05-01'; // Change this date
```

### **Modify Schedule**
Edit `cron_execute.sh` and update the crontab entry:

```bash
# Example: Run every 6 hours
0 */6 * * * /opt/homebrew/var/www/attend/cron_execute.sh
```

### **Add Email Notifications**
Edit `cron_config.php` and update the notification email:

```php
'notification_email' => 'your-email@domain.com',
```

## 📞 **Support**

If you encounter issues:

1. Check the log files in `/opt/homebrew/var/www/attend/application/logs/cron/`
2. Verify cron service is running: `sudo systemctl status cron`
3. Test manual execution via browser or curl
4. Check application logs in `/opt/homebrew/var/www/attend/application/logs/`

## 🎉 **Success Indicators**

- **Dashboard**: Attendance and roster data will display correctly
- **Logs**: Successful execution messages in cron logs
- **Database**: Records in `person_att_final` table
- **Timestamps**: Updated `last_gen` values in tracking table

---

**Note**: This cron job is designed to run automatically and requires minimal maintenance. Monitor the logs periodically to ensure it's running correctly.
