#!/bin/sh

# The Laravel project directory is the folder this script lives in, so the same
# file works in production and in test without edits. (It used to be a fixed
# production path, which made a test cron run production's scheduler.)
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

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
