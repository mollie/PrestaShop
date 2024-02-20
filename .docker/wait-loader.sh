#!/bin/bash

spinner() {
  local pid=$1
  local delay=0.1
  local spinstr='|/-\'
  while ps a | awk '{print $1}' | grep -q $pid; do
    local temp=${spinstr#?}
    printf " [%c] " "$spinstr"
    local spinstr=$temp${spinstr%"$temp"}
    sleep $delay
    printf "\b\b\b\b\b\b"
  done
  printf "    \b\b\b\b"
}

# Function to display loader with a message
show_loader() {
  local message=$1
  local port=$2
  shift 2
  echo -n "$message"
  ( "$@" ) &
  local loader_pid=$!
  local success=false
  while ps a | awk '{print $1}' | grep -q $loader_pid; do
    sleep 0.5
    echo -n "■"

    # Check if Docker container host returns a 302 (replace with your actual check)
    http_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$port")
    if [ "$http_status" -eq 302 ]; then
      success=true
      break
    fi
  done

  if [ "$success" = true ]; then
    echo " Done."
  else
    echo " Failed: Docker container host did not return a 302."
    # Optionally, you can add cleanup or error handling code here.
  fi
}

# Usage example with port as a parameter
if [ "$#" -lt 1 ]; then
  echo "Usage: $0 <port>"
  exit 1
fi

port=$1
show_loader "Building app containers. Please wait " "$port" sleep 120
