#!/bin/sh
set -e

cat <<EOF > /var/www/frontend/config.js
window.APP_CONFIG = {
    API_URL: "${API_URL}",
    FRONT_URL: "${FRONT_URL}",
};
EOF

exec nginx -g 'daemon off;'