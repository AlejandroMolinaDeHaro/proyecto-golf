#!/bin/bash
# Script para iniciar MySQL de XAMPP manualmente
# Úsalo si el servicio automático no funciona: bash start_mysql.sh

MYSQL_SAFE="/opt/lampp/bin/mysqld_safe"
PID_FILE="/opt/lampp/var/mysql/mysqld.pid"

if [ -f "$PID_FILE" ] && kill -0 $(cat "$PID_FILE") 2>/dev/null; then
    echo "MySQL ya está funcionando (PID: $(cat $PID_FILE))"
    exit 0
fi

echo "Iniciando MySQL..."
$MYSQL_SAFE --skip-syslog --datadir=/opt/lampp/var/mysql --pid-file=$PID_FILE &
sleep 2

if [ -f "$PID_FILE" ] && kill -0 $(cat "$PID_FILE") 2>/dev/null; then
    echo "MySQL iniciado correctamente (PID: $(cat $PID_FILE))"
else
    echo "ERROR: No se pudo iniciar MySQL"
    exit 1
fi
