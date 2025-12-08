#!/bin/bash

function stop_process {
    local pid_file=$1
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        kill "$pid"
        rm "$pid_file"
        echo "$pid_file stopped successfully"
    else
        echo "$pid_file not found"
    fi
}

for i in $(seq 0 5);
do
    stop_process "blad_async${i}_pid"
done



