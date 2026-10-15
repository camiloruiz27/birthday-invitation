#!/bin/sh

# Runs every minute from the hosting panel's cron (one line, never changes):
#
#   * * * * * /bin/sh /home/<user>/domains/<site>/public_html/cron-scheduler.sh
#
# What it does with that minute depends on whether anything needs doing, and
# deciding that costs almost nothing:
#
#  - Every five minutes (minute 0, 5, 10 ...) it runs the whole Laravel
#    scheduler: the heartbeat, payment reconciliation, the daily chores.
#  - On the other minutes it starts PHP ONLY when the application has said that
#    something is due (a timeline event, a queued job), and then it runs just the
#    tick. Otherwise it exits here, in a few milliseconds, without booting
#    Laravel at all.
#
# "Something is due" is a moment (epoch seconds) the application writes to
# storage/framework/next-run after every run (App\Modules\Immersion\Support\
# TickPlanner). A case has a new moment about every eleven minutes, so most
# minutes of a running game have nothing to do — and each run that does touch
# the database spends one of the host's 500 connections per hour.
#
# A missing, empty or unreadable file means "unknown", which means run the tick:
# the cost of a wrong guess is one wasted run, never a late envelope.

# The Laravel project directory is the folder this script lives in, so the same
# file works in production and in test without edits. (It used to be a fixed
# production path, which made a test cron run production's scheduler.)
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

# The PHP CLI executable (overridable so the script can be tested).
PHP_BIN="${PHP_BIN:-/opt/alt/php82/usr/bin/php}"

# The scheduler diagnostic log.
LOG_FILE="$PROJECT_DIR/storage/logs/cron-scheduler.log"

# The moment the application next needs PHP started.
NEXT_RUN_FILE="${NEXT_RUN_FILE:-$PROJECT_DIR/storage/framework/next-run}"

# The log is trimmed when it passes this many bytes (it used to grow forever:
# two lines a minute plus every command's output, for months).
LOG_MAX_BYTES="${LOG_MAX_BYTES:-2097152}"

# CRON_NOW / CRON_MINUTE exist only so the script can be tested at chosen times.
now="${CRON_NOW:-$(date +%s)}"

# Minute of the hour without a leading zero ("08" is not a number to every sh).
minute="${CRON_MINUTE:-$(date +%M)}"
minute=${minute#0}
[ -z "$minute" ] && minute=0

next=0
if [ -r "$NEXT_RUN_FILE" ]; then
    next=$(cat "$NEXT_RUN_FILE" 2>/dev/null)
fi
case "$next" in
    ''|*[!0-9]*) next=0 ;;
esac

if [ $((minute % 5)) -eq 0 ]; then
    COMMAND="schedule:run"
elif [ "$next" -le "$now" ]; then
    COMMAND="immersion:tick"
else
    # Nothing is due yet: do not even start PHP.
    exit 0
fi

# Move to the Laravel project directory
cd "$PROJECT_DIR" || exit 1

# Keep the log small: when it is over the limit, keep only its last lines.
if [ -f "$LOG_FILE" ]; then
    size=$(wc -c < "$LOG_FILE" 2>/dev/null || echo 0)
    case "$size" in
        ''|*[!0-9]*) size=0 ;;
    esac
    if [ "$size" -gt "$LOG_MAX_BYTES" ]; then
        tail -n 2000 "$LOG_FILE" > "$LOG_FILE.tmp" 2>/dev/null && mv "$LOG_FILE.tmp" "$LOG_FILE"
    fi
fi

# Log the Cron execution start
echo "[$(date '+%Y-%m-%d %H:%M:%S %z')] Cron started ($COMMAND)" >> "$LOG_FILE"

# Execute it
"$PHP_BIN" artisan $COMMAND >> "$LOG_FILE" 2>&1

EXIT_CODE=$?

# Log the Cron execution result
echo "[$(date '+%Y-%m-%d %H:%M:%S %z')] Cron finished with exit code $EXIT_CODE" >> "$LOG_FILE"

exit "$EXIT_CODE"
