#!/bin/bash

trap 'exit_handler' EXIT

ROOT="$(git rev-parse --show-toplevel)"
APP="$1"
DEFAULT_APP_URL="127.0.0.1:8049"

exit_handler ()
{
	log_info "Exiting ..."
}

log_info ()
{
	echo -e "$(tput bold)$(tput setaf 4)[INFO]$(tput sgr0)$(tput setaf 4) $1$(tput sgr0)"
}

log_error ()
{
	echo -e "$(tput bold)$(tput setaf 1)[ERROR]$(tput sgr0)$(tput setaf 1) $1$(tput sgr0)"
}

config ()
{
	value=$("$ROOT"/util/json_get_key.py "$ROOT"/config.json "$1")

	if [ $? -eq 0 ]; then
		echo "$value"
	else
		return 1
	fi
}

config_set ()
{
	"$ROOT"/util/json_set_key.py "$ROOT"/config.json "$1" "$2"
}

update_url ()
{
	url="$(echo "$(config "$1")" | sed -r 's/^(https?:\/\/)([^\/]+)(.*)$/\1'"$2"'\3/g')"
	config_set "$1" "$url"
}

app_url ()
{
	echo "$(config dev_server.url.$APP || echo "$DEFAULT_APP_URL")"
}

log_info "AXR Website PHP development server"

case "$APP" in
	www) DEFAULT_APP_URL="127.0.0.1:8040" ;;
	wiki) DEFAULT_APP_URL="127.0.0.1:8041" ;;
	hss) DEFAULT_APP_URL="127.0.0.1:8042" ;;

	*)
		log_error "Unknown mode"
		exit 1
		;;
esac

if [ "$APP" != "www" ]; then
	log_info "'$APP' depends on 'www' so make sure that it is running"
fi

log_info "Checking for memcached connectivity ..."
memcached-tool "$(config cache_servers.0.0):$(config cache_servers.0.1)" > /dev/null
if [ $? -ne 0 ]; then
	log_error "Could not connect to memcached server"
fi

log_info "Updating the configuration with new URLs"
update_url "url.$APP" "$(app_url)"

if [ "$APP" == "www" ]; then
	update_url "url.rsrc" "$(app_url)"
fi

log_info "Starting PHP server at $(app_url)"
log_info "If you want to use a domain name, you can use NGINX as a proxy"
php -S "$(app_url)" -t "${ROOT}/${APP}/www"
