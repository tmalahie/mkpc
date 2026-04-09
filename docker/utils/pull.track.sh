#!/bin/bash

# Script to pull circuit or arena data from production database
# Usage: ./pull.circuit.sh [circuits|arenes] <id>

set -e

# Check arguments
if [ $# -ne 2 ]; then
    echo "Usage: $0 [circuits|arenes] <id>"
    exit 1
fi

TABLE_TYPE=$1
ID=$2

# Validate table type
if [ "$TABLE_TYPE" != "circuits" ] && [ "$TABLE_TYPE" != "arenes" ]; then
    echo "Error: First parameter must be 'circuits' or 'arenes'"
    exit 1
fi

# Validate ID is numeric
if ! [[ "$ID" =~ ^[0-9]+$ ]]; then
    echo "Error: ID must be a number"
    exit 1
fi

# Get script directory
CURRENT_DIR=$(dirname $(realpath $0))

# Source .env file for production database credentials
if [ -f "$CURRENT_DIR/.env" ]; then
    source "$CURRENT_DIR/.env"
else
    echo "Error: .env file not found. Please create one with PROD_DB_* variables."
    exit 1
fi

# Check required environment variables
if [ -z "$PROD_DB_HOST" ] || [ -z "$PROD_DB_USER" ] || [ -z "$PROD_DB_PASSWORD" ] || [ -z "$PROD_DB_NAME" ]; then
    echo "Error: Missing required environment variables in .env file:"
    echo "  Required: PROD_DB_HOST, PROD_DB_USER, PROD_DB_PASSWORD, PROD_DB_NAME"
    echo "  Optional: PROD_DB_PORT (defaults to 3306)"
    exit 1
fi

PROD_DB_PORT=${PROD_DB_PORT:-3306}

# Local database credentials
LOCAL_DB_HOST="127.0.0.1"
LOCAL_DB_PORT="8306"
LOCAL_DB_USER="root"
LOCAL_DB_PASSWORD="root"
LOCAL_DB_NAME="mkpc"

# Determine table names
MAIN_TABLE="$TABLE_TYPE"
DATA_TABLE="${TABLE_TYPE}_data"

# Temporary files
TUNNEL_PID_FILE="/tmp/mkpc_tunnel_$$.pid"
SQL_FILE="/tmp/mkpc_pull_$$.sql"

# Cleanup function
cleanup() {
    # Kill SSH tunnel if it exists
    if [ -f "$TUNNEL_PID_FILE" ]; then
        TUNNEL_PID=$(cat "$TUNNEL_PID_FILE")
        if kill -0 "$TUNNEL_PID" 2>/dev/null; then
            kill "$TUNNEL_PID" 2>/dev/null || true
        fi
        rm -f "$TUNNEL_PID_FILE"
    fi
    # Remove temporary SQL file
    rm -f "$SQL_FILE"
}

# Set trap to cleanup on exit
trap cleanup EXIT INT TERM

# Create SSH tunnel to production database
echo "Creating SSH tunnel to production database..."
LOCAL_TUNNEL_PORT=$((33000 + RANDOM % 1000))

# Create tunnel in background and capture PID
ssh -N -L ${LOCAL_TUNNEL_PORT}:${PROD_DB_HOST}:${PROD_DB_PORT} ovh > /dev/null 2>&1 &
TUNNEL_PID=$!
echo $TUNNEL_PID > "$TUNNEL_PID_FILE"

# Wait a moment for tunnel to establish
sleep 2

# Check if tunnel is working
if ! kill -0 "$TUNNEL_PID" 2>/dev/null; then
    echo "Error: Failed to create SSH tunnel or tunnel process died"
    exit 1
fi

echo "SSH tunnel established on port $LOCAL_TUNNEL_PORT"

# Test tunnel connection
if ! mysql -h 127.0.0.1 -P ${LOCAL_TUNNEL_PORT} -u "$PROD_DB_USER" -p"$PROD_DB_PASSWORD" -e "SELECT 1" "$PROD_DB_NAME" > /dev/null 2>&1; then
    echo "Error: Cannot connect to production database through tunnel"
    exit 1
fi

# Export data from production database
echo "Exporting data from production database (${MAIN_TABLE} and ${DATA_TABLE} where id=$ID)..."

# Create SQL file with DELETE statements first
cat > "$SQL_FILE" <<EOF
-- Delete existing records if they exist
DELETE FROM \`${DATA_TABLE}\` WHERE id = ${ID};
DELETE FROM \`${MAIN_TABLE}\` WHERE id = ${ID};

EOF

# Export main table data
MAIN_DATA_FOUND=0
if mysqldump -h 127.0.0.1 -P ${LOCAL_TUNNEL_PORT} \
    -u "$PROD_DB_USER" -p"$PROD_DB_PASSWORD" \
    "$PROD_DB_NAME" \
    "$MAIN_TABLE" \
    --where="id=$ID" \
    --no-create-info \
    --skip-add-drop-table \
    --skip-triggers \
    --skip-lock-tables \
    --single-transaction \
    --compact >> "$SQL_FILE" 2>/dev/null; then
    # Check if mysqldump actually output INSERT statements (not just comments)
    if grep -q "^INSERT INTO" "$SQL_FILE"; then
        MAIN_DATA_FOUND=1
    fi
fi

if [ $MAIN_DATA_FOUND -eq 0 ]; then
    echo "Warning: No data found in ${MAIN_TABLE} for id=$ID"
fi

# Export data table
DATA_DATA_FOUND=0
if mysqldump -h 127.0.0.1 -P ${LOCAL_TUNNEL_PORT} \
    -u "$PROD_DB_USER" -p"$PROD_DB_PASSWORD" \
    "$PROD_DB_NAME" \
    "$DATA_TABLE" \
    --where="id=$ID" \
    --no-create-info \
    --skip-add-drop-table \
    --skip-triggers \
    --skip-lock-tables \
    --single-transaction \
    --compact >> "$SQL_FILE" 2>/dev/null; then
    # Check if mysqldump actually output INSERT statements
    if grep -q "^INSERT INTO" "$SQL_FILE"; then
        DATA_DATA_FOUND=1
    fi
fi

if [ $DATA_DATA_FOUND -eq 0 ]; then
    echo "Warning: No data found in ${DATA_TABLE} for id=$ID"
fi

# Check if we have any data to import
if [ $MAIN_DATA_FOUND -eq 0 ] && [ $DATA_DATA_FOUND -eq 0 ]; then
    echo "Error: No data found for id=$ID in ${MAIN_TABLE} or ${DATA_TABLE}"
    exit 1
fi

# Import into local database
echo "Importing data into local database..."
mysql -h "$LOCAL_DB_HOST" -P "$LOCAL_DB_PORT" \
    -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASSWORD" \
    "$LOCAL_DB_NAME" < "$SQL_FILE"

echo "Successfully imported ${TABLE_TYPE} data (id=$ID) from production to local database"

# Pull images from production file system
echo "Pulling images from production file system..."

# Determine image prefix based on table type
if [ "$TABLE_TYPE" = "circuits" ]; then
    IMAGE_PREFIX="map"
else
    IMAGE_PREFIX="course"
fi

# Set paths
PROD_IMAGE_DIR="/var/www/malahieude.net/mkpc/images/uploads"
LOCAL_IMAGE_DIR="$CURRENT_DIR/../php/images/uploads"

# Ensure local directory exists
mkdir -p "$LOCAL_IMAGE_DIR"

# Copy base image in all supported formats
BASE_IMAGE_COUNT=0
for ext in png jpg jpeg gif; do
    BASE_IMAGE="${IMAGE_PREFIX}${ID}.${ext}"
    if scp -q "ovh:${PROD_IMAGE_DIR}/${BASE_IMAGE}" "$LOCAL_IMAGE_DIR/" 2>/dev/null; then
        echo "  Copied ${BASE_IMAGE}"
        BASE_IMAGE_COUNT=$((BASE_IMAGE_COUNT + 1))
    fi
done

if [ $BASE_IMAGE_COUNT -eq 0 ]; then
    echo "  Warning: No base image found for ${IMAGE_PREFIX}${ID} (tried: png, jpg, jpeg, gif)"
fi

# Copy timestamped versions
TIMESTAMPED_COUNT=0
TIMESTAMPED_IMAGES=$(ssh ovh "sh -c 'for f in ${PROD_IMAGE_DIR}/${IMAGE_PREFIX}${ID}-*.*; do [ -f \"\$f\" ] && basename \"\$f\"; done' 2>/dev/null" 2>/dev/null || true)

if [ -n "$TIMESTAMPED_IMAGES" ]; then
    for img in $TIMESTAMPED_IMAGES; do
        if [ -n "$img" ]; then
            if scp -q "ovh:${PROD_IMAGE_DIR}/${img}" "$LOCAL_IMAGE_DIR/" 2>/dev/null; then
                echo "  Copied ${img}"
                TIMESTAMPED_COUNT=$((TIMESTAMPED_COUNT + 1))
            fi
        fi
    done
fi

if [ $TIMESTAMPED_COUNT -eq 0 ]; then
    echo "  No timestamped versions found"
fi

echo "Image pull completed"
