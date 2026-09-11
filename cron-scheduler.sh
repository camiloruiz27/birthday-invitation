#!/bin/sh

# Define the Laravel project directory
PROJECT_DIR="/home/u206029413/domains/cumplemiamor.cramultimedia.com/public_html"

# Define the PHP CLI executable
PHP_BIN="/opt/alt/php82/usr/bin/php"

# Define the scheduler diagnostic log
LOG_FILE="$PROJECT_DIR/storage/logs/cron-scheduler.log"

# Move to the Laravel project directory
cd "$PROJECT_DIR" || exit 1

# Log the Cron execution start
echo "[$(date '+%Y-%m-%d %H:%M:%S %z')] Cron started" >> "$LOG_FILE"

# Execute the Laravel scheduler
"$PHP_BIN" artisan schedule:run >> "$LOG_FILE" 2>&1

EXIT_CODE=$?

# Log the Cron execution result
echo "[$(date '+%Y-%m-%d %H:%M:%S %z')] Cron finished with exit code $EXIT_CODE" >> "$LOG_FILE"

exit "$EXIT_CODE"
