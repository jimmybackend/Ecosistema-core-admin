#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TEMPLATE_FILE="${ROOT_DIR}/.env.vm.example"
ENV_FILE="${ROOT_DIR}/.env"

if [[ ! -f "${TEMPLATE_FILE}" ]]; then
  echo "Error: no existe ${TEMPLATE_FILE}" >&2
  exit 1
fi

if [[ -f "${ENV_FILE}" ]]; then
  backup_file="${ENV_FILE}.bak.$(date +%Y%m%d-%H%M%S)"
  cp "${ENV_FILE}" "${backup_file}"
  echo "Respaldo creado: ${backup_file}"
else
  cp "${TEMPLATE_FILE}" "${ENV_FILE}"
  echo "Archivo .env creado desde .env.vm.example"
fi

set_managed_value() {
  local key="$1"
  local value="$2"
  local file="$3"
  local escaped_value

  escaped_value=$(printf '%s' "${value}" | sed -e 's/[\\&]/\\\\&/g')

  if grep -Eq "^[[:space:]]*${key}=" "${file}"; then
    sed -i -E "s|^[[:space:]]*${key}=.*$|${key}=${escaped_value}|" "${file}"
  else
    printf '\n%s=%s\n' "${key}" "${value}" >> "${file}"
  fi
}

prompt_hidden_required() {
  local prompt="$1"
  local secret=""
  while [[ -z "${secret}" ]]; do
    read -r -s -p "${prompt}" secret
    echo
    if [[ -z "${secret}" ]]; then
      echo "El valor no puede ser vacío."
    fi
  done
  printf '%s' "${secret}"
}

db_password="$(prompt_hidden_required 'DB_PASSWORD (oculto): ')"
invite_code="$(prompt_hidden_required 'CORE_REGISTRATION_INVITE_CODE (oculto): ')"

set_managed_value "DB_PASSWORD" "${db_password}" "${ENV_FILE}"
set_managed_value "CORE_REGISTRATION_INVITE_CODE" "${invite_code}" "${ENV_FILE}"

read -r -p "¿Actualizar APP_URL? (s/N): " update_app_url
if [[ "${update_app_url}" =~ ^[sS]$ ]]; then
  read -r -p "Nuevo APP_URL: " app_url
  if [[ -n "${app_url}" ]]; then
    set_managed_value "APP_URL" "${app_url}" "${ENV_FILE}"
  fi
fi

read -r -p "¿Actualizar SESSION_SECURE? (s/N): " update_session_secure
if [[ "${update_session_secure}" =~ ^[sS]$ ]]; then
  read -r -p "Nuevo SESSION_SECURE (true/false): " session_secure
  if [[ -n "${session_secure}" ]]; then
    set_managed_value "SESSION_SECURE" "${session_secure}" "${ENV_FILE}"
  fi
fi

read -r -p "¿Actualizar SESSION_IDLE_TIMEOUT? (s/N): " update_idle_timeout
if [[ "${update_idle_timeout}" =~ ^[sS]$ ]]; then
  read -r -p "Nuevo SESSION_IDLE_TIMEOUT (segundos): " idle_timeout
  if [[ -n "${idle_timeout}" ]]; then
    set_managed_value "SESSION_IDLE_TIMEOUT" "${idle_timeout}" "${ENV_FILE}"
  fi
fi

echo "Listo. .env actualizado de forma segura sin exponer secretos."
