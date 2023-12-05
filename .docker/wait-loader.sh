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
  shift
  echo -n "$message"
  ( "$@" ) &
  local loader_pid=$!
  while ps a | awk '{print $1}' | grep -q $loader_pid; do
    sleep 0.5
    echo -n "■"
  done
  echo " Done."
}

# Usage example
show_loader "Building app containers. Please wait " sleep 60
